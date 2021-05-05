<?php

namespace app\controllers;

use app\models\LogApi;
use Yii;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class Api extends BaseController
{
    public const CONSTANT_HASH = '20210505214200';
    public const TOKEN_PARAM = '_token';
    public const BLOG_PARAM = '_blog';

    public function afterAction($action, $result)
    {
        @LogApi::log();
        return parent::afterAction($action, $result);
    }

    public static function exceptionNotFoundHttp()
    {
        @LogApi::log(['response_http_code' => 404]);
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    public static function exceptionForbiddenHttp()
    {
        @LogApi::log(['response_http_code' => 403]);
        throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
    }
}
