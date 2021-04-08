<?php

namespace app\controllers;

use app\models\LogAdmin;
use Yii;
use yii\helpers\Url;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;

class Controller extends BaseController
{
    public function afterAction($action, $result)
    {
        @LogAdmin::log();
        return parent::afterAction($action, $result);
    }

    public function defaultBehaviors($rules)
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => 'yii\filters\AccessControl',
            'rules' => $rules,
            'denyCallback' => function ($rule, $action) {
                if (Yii::$app->user->isGuest) {
                    @LogAdmin::log(['response_http_code' => 403]);
                    Yii::$app->user->setReturnUrl(Url::current());
                    return $this->redirect(['/site/signin']);
                }
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        ];
        return $behaviors;
    }
}
