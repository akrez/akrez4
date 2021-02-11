<?php

namespace app\controllers;

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
        $isSuccessfull = null;
        //
        if ($id) {
            $model = Helper::findOrFail(Package::userValidQuery($id)->andWhere(['id' => $id])->andWhere(['product_id' => $parent_id]));
        } else {
            $model = null;
        }
        $newModel = new Package();
        $searchModel = new PackageSearch();
        $parentModel = Helper::findOrFail(Product::userValidQuery()->andWhere(['id' => $parent_id]));
        $parentSearchModel = new ProductSearch();
        //
        if ($state == 'create' && $newModel->load($post)) {
            $isSuccessfull = Helper::store($newModel, $post, [
                'product_id' => $parent_id,
                'user_name' => $parentModel->user_name,
            ]);
        } elseif ($state == 'update' && $model) {
            $isSuccessfull = Helper::store($model, $post, [
                'product_id' => $parent_id,
                'user_name' => $parentModel->user_name,
            ]);
        } elseif ($state == 'remove' && $model) {
            $isSuccessfull = Helper::delete($model);
        }
        if ($isSuccessfull) {
            $parentModel->updatePrice();
            $category = Category::userValidQuery()->where(['id' => $parentModel->category_id])->one();
            if ($category) {
                $category->updatePrice();
            }
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $parentModel);
        return $this->render('index', [
            'state' => $state,
        ] + compact('newModel', 'searchModel', 'parentModel', 'parentSearchModel', 'model', 'dataProvider'));
    }
}
