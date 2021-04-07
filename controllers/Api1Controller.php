<?php

namespace app\controllers;

use yii\data\Pagination;
use app\models\ProductField;
use app\components\SingleSort;
use yii\web\BadRequestHttpException;
use app\components\Cache;
use app\models\Blog;
use app\models\Category;
use app\models\Color;
use app\models\Customer;
use app\models\Field;
use app\models\FieldList;
use app\models\Gallery;
use app\models\Package;
use app\models\Product;
use app\models\Province;
use app\models\Search;
use app\models\Status;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class Api1Controller extends Api
{

    public const CONSTANT_HASH = '20210324172300';
    public const TOKEN_PARAM = '_token';
    public const BLOG_PARAM = '_blog';

    private static $_blog = false;

    public function init()
    {
        parent::init();
        Yii::$app->user->loginUrl = null;
        Yii::$app->user->enableSession = false;
        Yii::$app->user->enableAutoLogin = false;
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->charset = 'UTF-8';
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            $statusCode = $event->sender->statusCode;
            $data = (array) $event->sender->data;
            //
            $event->sender->data = [
                '_constant_hash' => self::CONSTANT_HASH,
                '_blog'          => (self::blog() ? self::blog()->toArray() : []),
                '_categories'    => self::categories(),
                '_customer'      => (Yii::$app->customerApi->getIdentity() ? Yii::$app->customerApi->getIdentity()->toArray() : []),
            ];
            //
            if ($statusCode == 200 && isset($data['code'])) {
                $event->sender->data['_code'] = $data['code'];
            } else {
                $event->sender->data['_code'] = $statusCode;
            }
            if (YII_DEBUG) {
                $event->sender->data += $data;
            }
        });
        if (empty(self::blog())) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
    }

    public static function blog()
    {
        if (self::$_blog !== false) {
            return self::$_blog;
        }

        self::$_blog = null;

        $blogName = Yii::$app->request->get(self::BLOG_PARAM, null);
        if ($blogName) {
            $blog = Blog::findBlogForApi($blogName);
            if ($blog) {
                self::$_blog = $blog;
            }
        }

        return self::$_blog;
    }

    public static function categories()
    {
        $blog = self::blog();
        if ($blog) {
            return Cache::getBlogCacheCategory($blog);
        }
        return [];
    }

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => 'yii\filters\auth\QueryParamAuth',
                'user' => Yii::$app->customerApi,
                'optional' => ['*'],
                'tokenParam' => self::TOKEN_PARAM,
            ],
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'user' => Yii::$app->customerApi,
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                },
                'rules' => [
                    [
                        'actions' => ['constant', 'search', 'product', 'info',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['signout', 'profile',  'basket', 'basket-add', 'basket-remove', 'invoice', 'invoice-add', 'invoice-view', 'invoice-remove',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['signin', 'signup', 'reset-password-request', 'reset-password',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }


    public function actionInfo()
    {
        return [];
    }

    public static function actionConstant()
    {
        return [
            'widget' => FieldList::widgetsList(),
            'opertaion' => FieldList::opertaionsList(),
            'color' => Color::getList(),
            'province' => Province::getList(),
        ];
    }

    public function actionSearch($category_id = null)
    {
        $blog = self::blog();
        //
        $page = Yii::$app->request->post('page');
        $page_size = Yii::$app->request->post('page_size');
        $sort = Yii::$app->request->post('sort');
        //
        $sortAttributes = [
            '-created_at' => Yii::t('app', 'Newest'),
            'created_at' => Yii::t('app', 'Oldest'),
            '-title' => Yii::t('app', 'Title (Desc)'),
            'title' => Yii::t('app', 'Title (Asc)'),
        ];
        ////
        $query = Product::findProductQueryForApi($blog->name);
        //
        $categories = self::categories();
        //
        if (empty($category_id)) {
            $category = null;
            $query->andWhere(['category_id' => array_keys($categories),]);
            $category_id = null;
        } else {
            if (!isset($categories[$category_id])) {
                throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
            }
            $category = Category::findCategoryForApi($blog->name, $category_id);
            if (!$category) {
                throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
            }
            $query->andWhere(['category_id' => $category_id,]);
        }
        //
        $allowedFieldsOfSections = [
            'Product' => ['title'],
            'Package' => ['price'],
            'ProductField' => boolval($category_id),
        ];
        //
        $search = [];
        $conditionsOfSections = [];
        foreach ($allowedFieldsOfSections as $section => $allowedFields) {
            $search[$section] = [];
            $conditionsOfSections[$section] = [];
            $postedDatas = (array)Yii::$app->request->post($section, []);
            foreach ($postedDatas as $postedField => $postedValues) {
                if ($allowedFields === true) {
                } elseif ($allowedFields === false) {
                    continue;
                } elseif (!in_array($postedField, $allowedFields)) {
                    continue;
                }
                $postedValues = (array)$postedValues;
                foreach ($postedValues as $postedValue) {
                    $searchModel = new Search();
                    $searchModel->load($postedValue, '');
                    $searchModel->field = $postedField;
                    if ($searchModel->validate()) {
                        $search[$section][$postedField][] = $searchModel->toArray();
                        //
                        $condition = [];
                        $condition[0] = $searchModel->operation;
                        $condition[1] = $searchModel->field;
                        if (in_array($searchModel->operation, FieldList::getPluralOperations())) {
                            $condition[2] = $searchModel->values;
                        } elseif (in_array($searchModel->operation, FieldList::getMinMaxOperations())) {
                            $condition[2] = $searchModel->value_min;
                            $condition[3] = $searchModel->value_max;
                        } else {
                            $condition[2] = $searchModel->value;
                        }
                        //
                        if ($section == 'ProductField') {
                            $condition[1] = 'value';
                            $condition = [
                                'AND',
                                ['blog_name' => $blog->name],
                                ['field' => $searchModel->field],
                                $condition,
                            ];
                            $conditionsOfSections[$section][] = [
                                'id' => ProductField::find()->select('product_id')->where($condition),
                            ];
                        } else {
                            $conditionsOfSections[$section][] = $condition;
                        }
                    }
                }
            }
        }

        if ($conditionsOfSections['Product']) {
            array_unshift($conditionsOfSections['Product'], 'AND');
            $query->andWhere($conditionsOfSections['Product']);
        } elseif ($conditionsOfSections['Package']) {
            array_unshift($conditionsOfSections['Package'], 'AND');
            $query->andWhere([
                'id' => Package::find()->select('product_id')->where(['status' => Status::STATUS_ACTIVE])->where($conditionsOfSections['ProductField']),
            ]);
        } elseif ($conditionsOfSections['ProductField']) {
            array_unshift($conditionsOfSections['ProductField'], 'AND');
            $query->andWhere($conditionsOfSections['ProductField']);
        }

        $products = [];
        $countOfResults = $query->count('id');

        $singleSort = new SingleSort([
            'sort' => $sort,
            'sortAttributes' => $sortAttributes,
        ]);

        $page_size = intval($page_size);
        if ($page_size == -1) {
            $page_size = $countOfResults;
        } elseif ($page_size > 0) {
            $page_size = $page_size;
        } else {
            $page_size = 12;
        }

        $pagination = new Pagination([
            'params' => [
                'page' => $page,
                'per-page' => $page_size,
            ],
            'totalCount' => $countOfResults,
        ]);

        if ($countOfResults > 0) {
            $products = $query->orderBy([$singleSort->attribute => $singleSort->order])->offset($pagination->offset)->limit($pagination->limit)->all();
            $products = ArrayHelper::toArray($products);
        }

        return [
            'categoryId' => $category_id,
            'category' => $category,
            'products' => $products,
            'fields' => Field::getFieldsList($category_id),
            'sort' => [
                'attribute' => $singleSort->sort,
                'attributes' => $singleSort->sortAttributes,
            ],
            'pagination' => [
                'page_count' => $pagination->getPageCount(),
                'page_size' => $pagination->getPageSize(),
                'page' => $pagination->getPage(),
                'total_count' => $countOfResults,
            ],
            'options' => ($category ? Cache::getCategoryCacheOptions($category) : []),
        ] + $search;
    }

    public function actionProduct($id)
    {
        $blog = self::blog();
        $categories = self::categories();

        $product = Product::findProductQueryForApi($blog->name)->andWhere([
            'AND', [
                'id' => $id,
                'category_id' => array_keys($categories)
            ]
        ])->one();
        if ($product) {
            $product = $product->toArray();
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $fields = Field::getFieldsList($product['category_id']);

        $images = Gallery::findProductGalleryQueryForApi($blog->name, $product['id'])->indexBy('name')->all();

        $packages = Package::findProductPackageQueryForApi($blog->name, $product['id'])->all();

        return [
            'categoryId' => $product['category_id'],
            'product' => $product,
            'fields' => $fields,
            'images' => ArrayHelper::toArray($images),
            'packages' => ArrayHelper::toArray($packages),
        ];
    }

    public function actionSignup()
    {
        $blog = self::blog();
        //
        $signup = Customer::signup(Yii::$app->request->post(), $blog->name);
        if ($signup == null) {
            throw new BadRequestHttpException();
        }
        if ($signup->hasErrors()) {
            return $signup->response();
        }
        return $signup->response(true);
    }

    public function actionSignout()
    {
        $signout = Yii::$app->customerApi->getIdentity();
        if (!$signout) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        $signout = $signout->signout();
        if ($signout == null) {
            throw new BadRequestHttpException();
        }
        return $signout->response();
    }

    public function actionSignin()
    {
        $blog = self::blog();
        //
        $signin = Customer::signin(Yii::$app->request->post(), $blog->name);
        if ($signin == null) {
            throw new BadRequestHttpException();
        }
        if ($user = $signin->getCustomer()) {
            return $user->response(true);
        }
        return $signin->response();
    }

    public function actionResetPasswordRequest()
    {
        $blog = self::blog();
        //
        $resetPasswordRequest = Customer::resetPasswordRequest(Yii::$app->request->post(), $blog->name);
        if ($resetPasswordRequest == null) {
            throw new BadRequestHttpException();
        }
        return $resetPasswordRequest->response();
    }

    public function actionResetPassword()
    {
        $blog = self::blog();
        //
        $resetPassword = Customer::resetPassword(Yii::$app->request->post(), $blog->name);
        if ($resetPassword == null) {
            throw new BadRequestHttpException();
        }
        return $resetPassword->response();
    }
}
