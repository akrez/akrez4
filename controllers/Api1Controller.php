<?php

namespace app\controllers;

use app\components\Cache;
use app\models\Blog;
use app\models\Category;
use app\models\Color;
use app\models\FieldList;
use app\models\Package;
use app\models\Product;
use app\models\Province;
use app\models\Search;
use app\models\Status;
use Yii;
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
                '_customer'      => (Yii::$app->customerApi->getIdentity() ? Yii::$app->customerApi->getIdentity()->info() : []),
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
        $search = [
            'SearchProduct' => [],
            'SearchPackage' => [],
            'SearchProductField' => [],
        ];
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
        if ($category_id) {
            $searchProductFields = (array)Yii::$app->request->post('SearchProductField', []);
            foreach ($searchProductFields as $searchProductFieldKey => $searchProductFieldValues) {
                $searchProductFieldValues = (array)$searchProductFieldValues;
                foreach ($searchProductFieldValues as $searchProductField) {
                    $searchModel = new Search();
                    $searchModel->load($searchProductField, '');
                    $searchModel->field = $searchProductFieldKey;
                    if ($searchModel->validate()) {
                        $search['SearchProductField'][$searchProductFieldKey][] = $searchModel->toArray();
                    }
                }
            }
        }
        //
        $searchProducts = (array)Yii::$app->request->post('SearchProduct', []);
        foreach ($searchProducts as $searchProductKey => $searchProductValues) {
            if (!in_array($searchProductKey, Search::$allowedSearchFieldsForApi['SearchProduct'])) {
                continue;
            }
            $searchProductValues = (array)$searchProductValues;
            foreach ($searchProductValues as $searchProduct) {
                $searchModel = new Search();
                $searchModel->load($searchProduct, '');
                $searchModel->field = $searchProductKey;
                if ($searchModel->validate()) {
                    $search['SearchProduct'][$searchProductKey][] = $searchModel->toArray();
                }
            }
        }
        //
        $searchPackages = (array)Yii::$app->request->post('SearchPackage', []);
        foreach ($searchPackages as $searchPackageKey => $searchPackageValues) {
            if (!in_array($searchPackageKey, Search::$allowedSearchFieldsForApi['SearchPackage'])) {
                continue;
            }
            $searchPackageValues = (array)$searchPackageValues;
            foreach ($searchPackageValues as $searchPackage) {
                $searchModel = new Search();
                $searchModel->load($searchPackage, '');
                $searchModel->field = $searchPackageKey;
                if ($searchModel->validate()) {
                    $search['SearchPackage'][$searchPackageKey][] = $searchModel->toArray();
                }
            }
        }

        $search[$fieldId] = [];
        if (!isset($searchParams[$fieldId]) || !is_array($searchParams[$fieldId])) {
            continue;
        }
        foreach ($searchParams[$fieldId] as $filter) {
            $model = new Search();
            $model->load($filter, '');
            $model->field = $fieldId;
            $model->type = $field['type'];
            $model->category_id = $field['category_id'];
            if ($model->validate()) {
                $search[$fieldId][] = $model->toArray();
            }
        }
        //

        $fieldStringHasFilter = false;
        $fieldStringQuery = FieldString::find()->select('product_id');

        $fieldNumberHasFilter = false;
        $FieldNumberQuery = FieldNumber::find()->select('product_id');

        foreach ($search as $field) {
            foreach ($field as $filter) {
                if ($filter['category_id']) {
                    if ($filter['type'] == FieldList::TYPE_STRING) {
                        $fieldStringHasFilter = true;
                        $fieldStringQuery->andFilterWhere(['AND', [$filter['operation'], 'value', $filter['_value']], ['=', 'field_id', $filter['field']]]);
                    } elseif ($filter['type'] == FieldList::TYPE_NUMBER) {
                        $fieldNumberHasFilter = true;
                        if ($filter['operation'] == FieldList::OPERATION_BETWEEN) {
                            $FieldNumberQuery->andFilterWhere(['AND', [$filter['operation'], 'value', $filter['_value'][0], $filter['_value'][1],], ['=', 'field_id', $filter['field']]]);
                        } else {
                            $FieldNumberQuery->andFilterWhere(['AND', [$filter['operation'], 'value', $filter['_value']], ['=', 'field_id', $filter['field']]]);
                        }
                    } elseif ($filter['type'] == FieldList::TYPE_BOOLEAN) {
                        $fieldNumberHasFilter = true;
                        $FieldNumberQuery->andFilterWhere(['AND', [$filter['operation'], 'value', $filter['_value']], ['=', 'field_id', $filter['field']]]);
                    }
                } elseif ($filter['field'] == 'title') {
                    $query->andFilterWhere([$filter['operation'], $filter['field'], $filter['_value']]);
                } elseif ($filter['field'] == 'price') {
                    if ($filter['operation'] == '<') {
                        $query->andFilterWhere([$filter['operation'], 'price_min', $filter['_value']]);
                    } elseif ($filter['operation'] == '>') {
                        $query->andFilterWhere([$filter['operation'], 'price_max', $filter['_value']]);
                    } elseif ($filter['operation'] == '=') {
                        $query->andFilterWhere(['OR', [$filter['operation'], 'price_min', $filter['_value']], [$filter['operation'], 'price_min', $filter['_value']]]);
                    } elseif ($filter['operation'] == '<>') {
                        $query->andFilterWhere(['AND', [$filter['operation'], 'price_min', $filter['_value']], [$filter['operation'], 'price_min', $filter['_value']]]);
                    } elseif ($filter['operation'] == 'IN') {
                        $query->andFilterWhere(['OR', [$filter['operation'], 'price_min', $filter['_value']], [$filter['operation'], 'price_min', $filter['_value']]]);
                    } elseif ($filter['operation'] == 'NOT IN') {
                        $query->andFilterWhere(['AND', [$filter['operation'], 'price_min', $filter['_value']], [$filter['operation'], 'price_min', $filter['_value']]]);
                    } elseif ($filter['operation'] == FieldList::OPERATION_BETWEEN) {
                        $query->andFilterWhere(['AND', ['>=', 'price_min', $filter['_value'][0]], ['<=', 'price_min', $filter['_value'][1]]]);
                    }
                }
            }
        }

        if ($fieldStringHasFilter) {
            $query->andWhere(['id' => $fieldStringQuery]);
        }

        if ($fieldNumberHasFilter) {
            $query->andWhere(['id' => $FieldNumberQuery]);
        }

        $products = [];
        $productsFields = [];
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
            $products = $query->orderBy([$singleSort->attribute => $singleSort->order])->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        }

        if ($countOfResults > 0 && $category_id) {
            $productsId = ArrayHelper::getColumn($products, 'id');
            if ($productsId) {
                $productFieldResults = array_merge(
                    FieldString::find()->where(['product_id' => $productsId])->all(),
                    FieldNumber::find()->where(['product_id' => $productsId])->all()
                );
                foreach ($productFieldResults as $productFieldResultKey => $productFieldResult) {
                    if (isset($productFields[$productFieldResult->field_id]['values']) == false) {
                        $productsFields[$productFieldResult->product_id][$productFieldResult->field_id] = ['values' => []] + $fields[$productFieldResult->field_id];
                    }
                    $productsFields[$productFieldResult->product_id][$productFieldResult->field_id]['values'][] = $productFieldResult->value;
                }
                foreach ($productsFields as $productFieldsKey => $productFields) {
                    usort($productsFields[$productFieldsKey], function ($a, $b) {
                        if ($a['seq'] === $b['seq']) {
                            return 1;
                        }
                        if ($b['seq'] === null) {
                            return 1;
                        }
                        if ($a['seq'] === null) {
                            return -1;
                        }
                        return ($a['seq'] > $b['seq']) ? 1 : -1;
                    });
                }
            }
        }

        return [
            '_categories' => $categories,
            'categoryId' => $category_id,
            'category' => $category,
            'products' => $products,
            'productsFields' => $productsFields,
            'search' => $search,
            'fields' => $fields,
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
