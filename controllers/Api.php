<?php

namespace app\controllers;

use app\models\LogApi;
use Throwable;
use Yii;
use yii\web\Controller as BaseController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class Api extends BaseController
{
    public const CONSTANT_HASH = '2022-03-23-14-05';
    public const TOKEN_PARAM = '_token';
    public const BLOG_PARAM = '_blog';

    public function afterAction($action, $result)
    {
        @LogApi::log();
        return parent::afterAction($action, $result);
    }

    public static function exceptionNotFoundHttp($e = null)
    {
        @LogApi::log([
            'response_http_code' => 404,
            'error_message' => ($e ? $e->getMessage() : null),
        ]);
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    public static function exceptionForbiddenHttp($e = null)
    {
        @LogApi::log([
            'response_http_code' => 403,
            'error_message' => ($e ? $e->getMessage() : null),
        ]);
        throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
    }

    public static function exceptionBadRequestHttp(Throwable $e = null)
    {
        @LogApi::log([
            'response_http_code' => 400,
            'error_message' => ($e ? $e->getLine() . '|' . $e->getMessage() . '|' . $e->getTraceAsString()  : null),
        ]);
        throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
    }
}
