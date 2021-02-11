<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Helper;
use app\models\Category;
use app\models\CategorySearch;
use app\models\Field;
use app\models\FieldSearch;
use app\models\ProductField;
use app\models\TextArea;
use Yii;

class FieldController extends Controller
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
        $textAreaModel = new TextArea();
        //
        if ($id) {
            $model = Helper::findOrFail(Field::userValidQuery($id)->andWhere(['id' => $id])->andWhere(['category_id' => $parent_id]));
        } else {
            $model = null;
        }
        $newModel = new Field();
        $searchModel = new FieldSearch();
        $parentModel = Helper::findOrFail(Category::userValidQuery()->andWhere(['id' => $parent_id]));
        $parentSearchModel = new CategorySearch();
        //
        $autoCompleteSource = array_keys(Cache::getCategoryCacheOptions($parentModel));
        $autoCompleteSource = array_map('strval', $autoCompleteSource);
        $textAreaModel->setValues($autoCompleteSource);
        //
        if ($state == 'batchSave' && $textAreaModel->load($post)) {
            $isSuccessfull = (bool) Field::batchSave($textAreaModel, $parentModel);
        } elseif ($state == 'update' && $model) {
            $isSuccessfull = Helper::store($model, $post, [
                'category_id' => $parent_id,
                'user_name' => $parentModel->user_name,
            ]);
        } elseif ($state == 'remove' && $model) {
            $isSuccessfull = Helper::delete($model);
        }
        if ($isSuccessfull) {
            Cache::updateProductFieldCache($parentModel->id);
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $parentModel);
        return $this->render('index', [
            'state' => $state,
            'textAreaModel' => $textAreaModel,
            'autoCompleteSource' => $autoCompleteSource,
        ] + compact('newModel', 'searchModel', 'parentModel', 'parentSearchModel', 'model', 'dataProvider'));
    }
}
