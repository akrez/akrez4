<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Helper;
use app\models\Category;
use app\models\CategorySearch;
use app\models\Product;
use app\models\TextArea;
use Yii;

class CategoryController extends Controller
{

    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['index',],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ],
        ]);
    }

    public function actionIndex($id = null)
    {
        $id = empty($id) ? null : intval($id);
        $post = Yii::$app->request->post();
        $state = Yii::$app->request->get('state', '');
        $updateCacheNeeded = null;
        $textAreaModel = new TextArea();
        //
        $model = null;
        $newModel = new Category();
        $searchModel = new CategorySearch();
        //
        if ($state == 'update' && $id) {
            $model = Helper::findOrFail(Category::userValidQuery($id));
            $oldStatus = $model->status;
            $isSuccessfull = Helper::store($model, $post, [
                'user_name' => Yii::$app->user->getId(),
            ]);
        } elseif ($state == 'batchSave' && $textAreaModel->load($post)) {
            $lines = $textAreaModel->explodeLines();
            $errors = Category::batchSave($lines, $id);
            if ($errors) {
                $textAreaModel->addErrors(['values' => $errors]);
                $textAreaModel->setValues($lines);
            } else {
                $textAreaModel = new TextArea();
                $updateCacheNeeded = true;
            }
        } elseif ($state == 'remove' && $id) {
            $model = Helper::findOrFail(Category::userValidQuery($id));
            $products = Product::userValidQuery()->where(['category_id' => $id])->all();
            if ($products) {
                $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($products), 'child' => Yii::t('app', 'Product'), 'parent' => Yii::t('app', 'Category')]);
                Yii::$app->session->setFlash('danger', $msg);
            } else {
                $updateCacheNeeded = Helper::delete($model);
            }
        } else {
            $state = '';
        }
        if ($updateCacheNeeded) {
            Cache::updateUserCacheCategory(Yii::$app->user->getIdentity());
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, null);
        return $this->render('index', ['state' => $state, 'textAreaModel' => $textAreaModel,] + compact('newModel', 'searchModel', 'model', 'dataProvider'));
    }
}
