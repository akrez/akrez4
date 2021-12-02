<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Helper;
use app\models\FinancialAccount;
use app\models\FinancialAccountSearch;
use Yii;

/**
 * FinancialAccountController implements the CRUD actions for FinancialAccount model.
 */
class FinancialAccountController extends Controller
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
            $model = Helper::findOrFail(FinancialAccount::blogValidQuery($id)->andWhere(['id' => $id]));
        } else {
            $model = null;
        }
        $newModel = new FinancialAccount();
        $searchModel = new FinancialAccountSearch();
        //
        if ($state == 'create' && $newModel->load($post)) {
            $updateCacheNeeded = Helper::store($newModel, $post, [
                'blog_name' => Yii::$app->user->getId(),
            ]);
        } elseif ($state == 'update' && $model) {
            $updateCacheNeeded = Helper::store($model, $post, [
                'blog_name' => Yii::$app->user->getId(),
            ]);
        } elseif ($state == 'remove' && $model) {
            $updateCacheNeeded = Helper::delete($model);
        }
        if ($updateCacheNeeded) {
            $newModel = new FinancialAccount();
            Cache::updateBlogCacheAccount(Yii::$app->user->getIdentity());
        }
        //
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', ['state' => $state] + compact('newModel', 'searchModel', 'model', 'dataProvider'));
    }
}
