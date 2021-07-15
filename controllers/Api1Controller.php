<?php

namespace app\controllers;

use yii\data\Pagination;
use app\models\ProductField;
use app\components\SingleSort;
use yii\web\BadRequestHttpException;
use app\components\Cache;
use app\components\Sms;
use app\models\Basket;
use app\models\Blog;
use app\models\Category;
use app\models\Color;
use app\models\Customer;
use app\models\Field;
use app\models\FieldList;
use app\models\Gallery;
use app\models\Language;
use app\models\LogApi;
use app\models\Package;
use app\models\Page;
use app\models\Product;
use app\models\Province;
use app\models\Search;
use app\models\Status;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class Api1Controller extends Api
{
    private static $_blog = false;
    private static $_customer = false;

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
            $data = $event->sender->data;
            //
            if (Yii::$app->response->format == Response::FORMAT_JSON) {
                $data = (array) $data;
                $event->sender->data = [
                    '_constant_hash' => self::CONSTANT_HASH,
                    '_blog'          => (self::blog() ? self::blog()->toArray() : []),
                    '_categories'    => self::categories(),
                    '_customer'      => (Yii::$app->customerApi->getIdentity() ? Yii::$app->customerApi->getIdentity()->toArray() : []),
                ];
                if ($statusCode == 200 && isset($data['code'])) {
                    $event->sender->data['_code'] = $data['code'];
                } else {
                    $event->sender->data['_code'] = $statusCode;
                }
                if ($statusCode == 200 || YII_DEBUG) {
                    $event->sender->data += $data;
                }
            } else {
                if ($statusCode == 200 || YII_DEBUG) {
                } else {
                    $event->sender->data = null;
                }
            }
        });
        if (empty(self::blog())) {
            Api::exceptionNotFoundHttp();
        }
        Yii::$app->language = self::blog()->language;
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

    /**
     * @return Customer|null
     */
    public static function customer()
    {
        if (self::$_customer !== false) {
            return self::$_customer;
        }

        self::$_customer = null;

        if (Yii::$app->customerApi->getIdentity()) {
            self::$_customer = Yii::$app->customerApi->getIdentity();
        }

        return self::$_customer;
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
                    Api::exceptionForbiddenHttp();
                },
                'rules' => [
                    [
                        'actions' => ['constant', 'index', 'category', 'product', 'info', 'page'],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['signout', 'profile',  'basket', 'basket-add', 'basket-delete', 'invoice', 'invoice-add', 'invoice-view', 'invoice-remove',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'verify-request', 'verify', 'reset-password-request', 'reset-password'],
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
        $result = [];
        foreach (Language::getList() as $languageKey => $language) {
            Yii::$app->language = $languageKey;
            $result[$languageKey] = [
                'widget' => FieldList::widgetsList(),
                'opertaion' => FieldList::opertaionsList(),
                'province' => Province::getList(),
                'language' => Language::getList(),
                'entity_page' => Page::entityPage(),
            ];
        }
        return ['constant' => $result];
    }

    public function actionIndex()
    {
        return $this->search(Yii::$app->request->post());
    }

    public function actionCategory($category_id)
    {
        return $this->search(Yii::$app->request->post(), $category_id);
    }

    public function search($options = [], $category_id = null)
    {
        $options = (array)$options + [
            'page' => null,
            'page_size' => null,
            'sort' => null,
        ];
        $page = $options['page'];
        $page_size = $options['page_size'];
        $sort = $options['sort'];
        //
        $blog = self::blog();
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
                Api::exceptionNotFoundHttp();
            }
            $category = Category::findCategoryForApi($blog->name, $category_id);
            if (!$category) {
                Api::exceptionNotFoundHttp();
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
            $product->increaseView();
            $product = $product->toArray();
        } else {
            Api::exceptionNotFoundHttp();
        }

        $fields = Field::getFieldsList($product['category_id']);

        $images = Gallery::findProductGalleryQueryForApi($blog->name, $product['id'])->indexBy('name')->all();

        $packages = Package::findPackageQueryForApi($blog->name)
            ->andWhere(['product_id' => $product['id']])
            ->all();

        LogApi::setData(['model_category_id' => $product['category_id']]);

        return [
            'categoryId' => $product['category_id'],
            'product' => $product,
            'fields' => $fields,
            'images' => ArrayHelper::toArray($images),
            'packages' => ArrayHelper::toArray($packages),
        ];
    }

    public function actionPage($entity = null, $page_type = null, $entity_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        $blog = self::blog();
        $page = Page::findPageQueryForApi($blog->name, $entity, $page_type, $entity_id)->one();
        if (!$page) {
            Api::exceptionNotFoundHttp();
        }
        return $page->body;
    }

    public static function actionBasketAdd($add = true)
    {
        $blog = self::blog();
        $customer = self::customer();
        $post = \Yii::$app->request->post();
        //
        $basket = new Basket();
        $basket->load($post, '');
        $basket->blog_name = $blog->name;
        $basket->customer_id = $customer->id;
        if ($basket->validate()) {
            $basketHandler = Basket::findDuplicateForApi($blog->name, $customer->id, $basket->package_id);
            if ($basketHandler) {
                $basketHandler->cnt = $basket->cnt + ($add ? $basketHandler->cnt : 0);
                $basket = $basketHandler;
            }
        }
        $basket->save();
        return [
            'package' => ($basket->_package ? $basket->_package->toArray() : null),
            'basket' => $basket->response(),
        ];
    }

    public static function actionBasket()
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $baskets = [];
        $packages = [];
        $products = [];
        //
        $basketModels = Basket::findBasketQueryForApi($blog->name, $customer->id)->andWhere([
            'package_id' => Package::findPackageFullQueryForApi($blog->name)->select('id')
        ])->all();
        //
        $packageIds = ArrayHelper::getColumn($basketModels, 'package_id');
        Package::getFullPackagesForApiWithCache($blog->name, $packageIds);

        $productIds = [];
        foreach ($basketModels as $basketModel) {
            if ($basketModel->validate() && $basketModel->hasNewPrice) {
                $basketModel->save();
            }
            $baskets[$basketModel->id] = $basketModel->response();
            if ($basketModel->_package) {
                $packages[$basketModel->_package->id] = $basketModel->_package;
                $productIds[] = $basketModel->_package->product_id;
            }
        }

        $products = Product::find()->where(['id' => $productIds])->indexBy('id')->all();
        $products = ArrayHelper::toArray($products);

        return [
            'baskets' => $baskets,
            'packages' => $packages,
            'products' => $products,
        ];
    }

    public static function actionBasketDelete($package_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $status = false;
        $basket = Basket::findDuplicateForApi($blog->name, $customer->id, $package_id);
        if ($basket) {
            $status = $basket->delete();
        }

        return [
            'status' => $status,
        ];
    }

    public function actionLogin()
    {
        $post = \Yii::$app->request->post();
        $blog = self::blog();
        //
        $signup = Customer::signup($blog->name, $post);
        if ($signup == null) {
            Api::exceptionBadRequestHttp();
        }
        //
        if (!$signup->hasErrors()) {
            return $signup->response('verify-request', true);
        }
        //
        $login = $signup->getCustomer();
        if (!$login) {
            return $signup->response('login', false);
        }
        $signup->clearErrors();
        //
        $login->setScenario('login');
        $login->load($post, '');
        if ($login->validate()) {
            return $login->response('index', true, true);
        }
        $signup->addErrors(['password' => $login->getErrorSummary(true)]);
        if ($login->status == Customer::SIGNUP_STATUS) {
            return $signup->response('verify-request', false);
        }
        return $signup->response('login', false);
    }

    public function actionVerifyRequest()
    {
        $blog = self::blog();
        //
        $verifyRequest = new Customer(['scenario' => 'verifyRequest']);
        try {
            $verifyRequest->load(\Yii::$app->request->post(), '');
            $verifyRequest->blog_name = $blog->name;
            if ($verifyRequest->validate()) {
                $user = $verifyRequest->getCustomer();
                $user->setAttributeToken('verify_token', 'verify_at');
                if ($user->save(false)) {
                    Sms::customerVerifyRequest($user, $blog);
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp();
        }
        return $verifyRequest->response();
    }

    public function actionVerify()
    {
        $blog = self::blog();
        $post = \Yii::$app->request->post();
        //
        $verify = new Customer(['scenario' => 'verify']);
        try {
            $verify->load($post, '');
            $verify->blog_name = $blog->name;
            if ($verify->validate()) {
                $user = $verify->getCustomer();
                $user->setAttributeToken('verify_token', 'verify_at', true);
                $user->status = Status::STATUS_ACTIVE;
                if ($user->save(false)) {
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp();
        }
        return $verify->response();
    }

    public function actionSignout()
    {
        $signout = Yii::$app->customerApi->getIdentity();
        if (!$signout) {
            Api::exceptionNotFoundHttp();
        }
        $signout->setAuthKey();
        if ($signout->save(false)) {
            return $signout->response();
        }
        Api::exceptionBadRequestHttp();
    }

    public function actionResetPasswordRequest()
    {
        $blog = self::blog();
        //
        $resetPasswordRequest = new Customer(['scenario' => 'resetPasswordRequest']);
        try {
            $resetPasswordRequest->load(\Yii::$app->request->post(), '');
            $resetPasswordRequest->blog_name = $blog->name;
            if ($resetPasswordRequest->validate()) {
                $user = $resetPasswordRequest->getCustomer();
                $user->setAttributeToken('reset_token', 'reset_at');
                if ($user->save(false)) {
                    Sms::customerResetPasswordRequest($user, $blog);
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp();
        }
        return $resetPasswordRequest->response();
    }

    public function actionResetPassword()
    {
        $blog = self::blog();
        //
        $resetPassword = new Customer(['scenario' => 'resetPassword']);
        try {
            $resetPassword->load(\Yii::$app->request->post(), '');
            $resetPassword->blog_name = $blog->name;
            if ($resetPassword->validate()) {
                $user = $resetPassword->getCustomer();
                $user->setAttributeToken('reset_token', 'reset_at', true);
                $user->setPasswordHash($resetPassword->password);
                if ($user->save(false)) {
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp();
        }
        return $resetPassword->response();
    }
}
