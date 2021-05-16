<?php

namespace app\controllers;

use app\components\Cache;
use Yii;
use app\components\Helper;
use app\models\Page;
use app\models\Status;
use yii\web\NotFoundHttpException;

class PageController extends Controller
{
    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['index', 'create', 'view', 'delete', 'update',],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ]
        ]);
    }

    public function actionIndex($entity, $entity_id)
    {
        $post = Yii::$app->request->post();

        $page = Page::blogValidQuery()->andWhere(['entity' => $entity, 'entity_id' => $entity_id,])->one();
        if (empty($page)) {
            $page = new Page();
            $page->entity = $entity;
            $page->entity_id = $entity_id;
        }

        $entityModel = $page->setEntity($entity, $entity_id);
        if ($entityModel === false) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        if ($page->load(Yii::$app->request->post())) {
            $refreshNeeded = Helper::store($page, $post, [
                'blog_name' => Yii::$app->user->getId(),
                'status' => $page->isNewRecord ? Status::STATUS_ACTIVE : $page->status,
            ]);
            if ($refreshNeeded) {
                Cache::updateCachePages($entityModel, $page);
                return $this->refresh();
            }
        }

        return $this->render('index', [
            'model' => $page,
            'entityModel' => $entityModel,
        ]);
    }
}
