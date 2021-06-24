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
        $updateCacheNeeded = null;
        $textAreaModel = new TextArea();
        //
        if ($id) {
            $model = Helper::findOrFail(Field::blogValidQuery($id)->andWhere(['id' => $id])->andWhere(['category_id' => $parent_id]));
        } else {
            $model = null;
        }
        $searchModel = new FieldSearch();
        $parentModel = Helper::findOrFail(Category::blogValidQuery()->andWhere(['id' => $parent_id]));
        $parentSearchModel = new CategorySearch();
        //
        $autoCompleteSource = array_keys(Cache::getCategoryCacheOptions($parentModel));
        $autoCompleteSource = array_map('strval', $autoCompleteSource);
        $textAreaModel->setValues($autoCompleteSource);
        //
        if ($state == 'batchSave' && $textAreaModel->load($post)) {
            $updateCacheNeeded = (bool) Field::batchSave($textAreaModel, $parentModel);
            if (empty($textAreaModel->explodeLines())) {
                $textAreaModel->setValues($autoCompleteSource);
            }
        } elseif ($state == 'update' && $model) {
            $updateCacheNeeded = Helper::store($model, $post, [
                'blog_name' => $parentModel->blog_name,
            ]);
        } elseif ($state == 'remove' && $model) {
            $updateCacheNeeded = Helper::delete($model);
        }
        if ($updateCacheNeeded) {
            Cache::updateProductsCacheField($parentModel);
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $parentModel);
        return $this->render('index', [
            'state' => $state,
            'textAreaModel' => $textAreaModel,
            'autoCompleteSource' => $autoCompleteSource,
        ] + compact('searchModel', 'parentModel', 'parentSearchModel', 'model', 'dataProvider'));
    }
}
