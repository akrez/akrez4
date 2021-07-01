<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Helper;
use app\models\ColorSearch;
use app\models\Product;
use app\models\Color;
use app\models\ProductSearch;
use Yii;

class ColorController extends Controller
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

    public function actionIndex($id = null)
    {
        $id = empty($id) ? null : intval($id);
        $post = Yii::$app->request->post();
        $state = Yii::$app->request->get('state', '');
        $updateCacheNeeded = null;
        //
        if ($id) {
            $model = Helper::findOrFail(Color::blogValidQuery($id)->andWhere(['id' => $id]));
        } else {
            $model = null;
        }
        $newModel = new Color();
        $searchModel = new ColorSearch();
        //
        if ($state == 'create' && $newModel->load($post)) {
            $updateCacheNeeded = Helper::store($newModel, $post, [
                'blog_name' => Yii::$app->user->getId(),
            ]);
        } elseif ($state == 'update' && $model) {
            $updateCacheNeeded = Helper::store($model, $post, [
                'blog_name' => Yii::$app->user->getId(),
            ]);
        }
        if ($updateCacheNeeded) {
            $newModel = new Color();
            Cache::updateBlogCacheColor(Yii::$app->user->getIdentity());
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $colorRawList = Color::getRawList();
        return $this->render('index', ['state' => $state, 'colorRawList' => $colorRawList] + compact('newModel', 'searchModel', 'model', 'dataProvider'));
    }
}