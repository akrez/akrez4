<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Helper;
use app\models\Package;
use app\models\PackageSearch;
use app\models\Product;
use app\models\Category;
use app\models\ProductSearch;
use Yii;

class PackageController extends Controller
{

    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['index'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ]
        ]);
    }

    public function actionIndex($parent_id, $id = null)
    {
        $id = empty($id) ? null : intval($id);
        $parent_id = intval($parent_id);
        $post = Yii::$app->request->post();
        $state = Yii::$app->request->get('state', '');
        $updateCacheNeeded = null;
        //
        if ($id) {
            $model = Helper::findOrFail(Package::blogValidQuery($id)->andWhere(['id' => $id])->andWhere(['product_id' => $parent_id]));
        } else {
            $model = null;
        }
        $newModel = new Package();
        $searchModel = new PackageSearch();
        $parentModel = Helper::findOrFail(Product::blogValidQuery()->andWhere(['id' => $parent_id]));
        $parentSearchModel = new ProductSearch();
        //
        if ($state == 'create' && $newModel->load($post)) {
            $updateCacheNeeded = Helper::store($newModel, $post, [
                'product_id' => $parent_id,
                'blog_name' => $parentModel->blog_name,
            ]);
        } elseif ($state == 'update' && $model) {
            $updateCacheNeeded = Helper::store($model, $post, [
                'product_id' => $parent_id,
                'blog_name' => $parentModel->blog_name,
            ]);
        } elseif ($state == 'remove' && $model) {
            $updateCacheNeeded = Helper::delete($model);
        }
        if ($updateCacheNeeded) {
            $newModel = new Package();
            Cache::updateProductPrice($parentModel);
            $category = Category::blogValidQuery()->where(['id' => $parentModel->category_id])->one();
            if ($category) {
                Cache::updateCategoryPrice($category);
            }
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $parentModel);
        return $this->render('index', [
            'state' => $state,
        ] + compact('newModel', 'searchModel', 'parentModel', 'parentSearchModel', 'model', 'dataProvider'));
    }
}
