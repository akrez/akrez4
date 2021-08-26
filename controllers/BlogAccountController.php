<?php

namespace app\controllers;

use app\components\Helper;
use app\models\BlogAccount;
use app\models\BlogAccountSearch;
use Yii;

/**
 * BlogAccountController implements the CRUD actions for BlogAccount model.
 */
class BlogAccountController extends Controller
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
            $model = Helper::findOrFail(BlogAccount::blogValidQuery($id)->andWhere(['id' => $id]));
        } else {
            $model = null;
        }
        $newModel = new BlogAccount();
        $searchModel = new BlogAccountSearch();
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
            $newModel = new BlogAccount();
            //Cache::updateBlogCacheColor(Yii::$app->user->getIdentity());
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', ['state' => $state] + compact('newModel', 'searchModel', 'model', 'dataProvider'));
    }
}
