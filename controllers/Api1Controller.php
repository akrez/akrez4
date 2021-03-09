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
use app\models\Field;
use app\models\FieldList;
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

    public const CONSTANT_HASH = '20210226174900';
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

    public function actionSearch($category_id = null, $page = null, $page_size = null, $sort = null)
    {
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
                        $condition = [
                            0 => $searchModel->operation,
                            1 => $searchModel->field,
                        ];
                        if (in_array($searchModel->operation, FieldList::getMinMaxOperations())) {
                            $condition[2] = $searchModel->_value[0];
                            $condition[3] = $searchModel->_value[1];
                        } else {
                            $condition[2] = $searchModel->_value;
                        }
                        $conditionsOfSections[$section][] = $condition;
                    }
                }
            }
        }

        if ($conditionsOfSections['Product']) {
            array_unshift($conditionsOfSections['Product'], 'AND');
            $query->where($conditionsOfSections['Product']);
        } elseif ($conditionsOfSections['Package']) {
            array_unshift($conditionsOfSections['Package'], 'AND');
            $query->where($conditionsOfSections['Package']);
        } elseif ($conditionsOfSections['ProductField']) {
            array_unshift($conditionsOfSections['ProductField'], 'AND');
            $query->andWhere([
                'id' => ProductField::find()->select('product_id')->where($conditionsOfSections['ProductField']),
            ]);
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
            $products =ArrayHelper::toArray($products);
        }

        return [
            'categoryId' => $category_id,
            'category' => $category,
            'products' => $products,
            'search' => $search,
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
        ];
    }
}
