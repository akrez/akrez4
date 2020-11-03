<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;

class Controller extends BaseController
{
    public $wizard;

    public function defaultBehaviors($rules)
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => 'yii\filters\AccessControl',
            'rules' => $rules,
            'denyCallback' => function ($rule, $action) {
                if (Yii::$app->user->isGuest) {
                    Yii::$app->user->setReturnUrl(Url::current());
                    return $this->redirect(['/site/signin']);
                }
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        ];
        return $behaviors;
    }
}
