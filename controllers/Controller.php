<?php

namespace app\controllers;

use app\models\LogAdmin;
use Yii;
use yii\base\Application;
use yii\helpers\Url;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;

class Controller extends BaseController
{
    public function init()
    {
        parent::init();
        Yii::$app->on(Application::EVENT_AFTER_REQUEST, function ($event) {
            @LogAdmin::log();
        });
    }

    public function defaultBehaviors($rules)
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => 'yii\filters\AccessControl',
            'rules' => $rules,
            'denyCallback' => function ($rule, $action) {
                if (Yii::$app->user->isGuest) {
                    @LogAdmin::setData(['response_http_code' => 403]);
                    Yii::$app->user->setReturnUrl(Url::current());
                    return $this->redirect(['/site/signin']);
                }
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        ];
        return $behaviors;
    }
}
