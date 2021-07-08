<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Sms;
use app\components\Jdf;
use app\models\Gallery;
use app\models\Status;
use app\models\Blog;
use app\models\LogApi;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

class BlogController extends Controller
{
    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['index', 'profile'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ],
        ]);
    }

    public function actionIndex()
    {
        $createdDateFrom = Jdf::jdate('Y-m-d H:i:s', strtotime(-30 . " days"));

        $logApiFilterModel = new LogApi();
        $logApiFilterModel->load(Yii::$app->request->get());
        $logApiFilterModel->blog_name = Blog::print('name');
        $logApiFilterModel->created_date_from = $createdDateFrom;
        $logApiFilterModel->response_http_code = 200;

        $dates = [];
        for ($d = 0; $d <= 29; $d++) {
            $pastDaysTimeStamp = strtotime(($d - 29) . " days");
            $dates[] = Jdf::jdate('Y-m-d', $pastDaysTimeStamp);
        }

        return $this->render('index', [
            'dates' => $dates,
            'groupedDatas' => $logApiFilterModel->statQueryGrouped()->asArray()->all(),
            'dataProvider' => new ActiveDataProvider([
                'query' => $logApiFilterModel->statQuery(),
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
            ]),
            'list' => [
                'categories' => Cache::getBlogCacheCategory(Yii::$app->user->getIdentity()),
            ],
            'logApiFilterModel' => $logApiFilterModel,
        ]);
    }

    public function actionProfile()
    {
        $post = Yii::$app->request->post();
        $state = Yii::$app->request->get('state', 'update');
        $updateCacheNeeded = false;
        //
        $model = \Yii::$app->user->getIdentity();
        $model->setScenario('profile');
        //
        if ($state == 'update' && $model->load($post)) {
            $updateCacheNeeded = $model->save();
        } elseif ($state == 'password' && $model->load($post)) {
            if ($model->password) {
                $model->setPasswordHash($model->password);
                $model->password = '';
                $updateCacheNeeded = $model->save();
            }
        } elseif ($state == 'galleryUpload' && $model) {
            if ($model->image = UploadedFile::getInstance($model, 'image')) {
                $gallery = Gallery::upload($model->image->tempName, Gallery::TYPE_LOGO, null, [
                    'mode' => Yii::$app->request->post('mode')
                ]);
                if ($gallery->hasErrors()) {
                    $model->addErrors(['image' => $gallery->getErrorSummary(true)]);
                } else {
                    if (empty($model->logo)) {
                        $model->logo = $gallery->name;
                        $model->save();
                    }
                    $model->image = null;
                }
            }
        } elseif ($state == 'galleryDelete' && $model && ($name = Yii::$app->request->get('name'))) {
            $gallery = Gallery::find()->where([
                'AND',
                ['name' => $name],
                ['blog_name' => $model->name],
                ['type' => Gallery::TYPE_LOGO],
            ])->one();
            if ($gallery) {
                $gallery->delete();
            }
        } elseif ($state == 'galleryDefault' && $model && ($name = Yii::$app->request->get('name'))) {
            $gallery = Gallery::find()->where([
                'AND',
                ['name' => $name],
                ['blog_name' => $model->name],
                ['type' => Gallery::TYPE_LOGO],
            ])->one();
            if ($gallery) {
                $model->logo = $gallery->name;
                $model->save();
            }
        } else {
            $state = 'update';
        }

        if ($updateCacheNeeded) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'alertUpdateSuccessfull'));
        }

        return $this->render('profile', [
            'state' => $state,
            'model' => $model,
        ]);
    }
}
