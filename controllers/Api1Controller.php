<?php

namespace app\controllers;

use app\components\Cache;
use app\models\User;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class Api1Controller extends Api
{

    public const CONSTANT_HASH = '20210226174900';
    public const TOKEN_PARAM = '_token';
    public const USER_PARAM = '_user';

    private static $_user = false;

    public function init()
    {
        parent::init();
        Yii::$app->user->loginUrl = null;
        Yii::$app->user->enableSession = false;
        Yii::$app->user->enableAutoLogin = false;
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->charset = 'UTF-8';
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            $statusCode = $event->sender->statusCode;
            $data = (array) $event->sender->data;
            //
            $event->sender->data = [
                '_constant_hash' => self::CONSTANT_HASH,
                '_user'          => (self::user() ? self::user()->info() : []),
                '_categories'    => (self::user() ? Cache::getUserCacheCategory(self::user()) : []),
                '_customer'      => (Yii::$app->customerApi->getIdentity() ? Yii::$app->customerApi->getIdentity()->info() : []),
            ];
            //
            if ($statusCode == 200 && isset($data['code'])) {
                $event->sender->data['_code'] = $data['code'];
            } else {
                $event->sender->data['_code'] = $statusCode;
            }
        });
        if (empty(self::user())) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
    }

    public static function user()
    {
        if (self::$_user !== false) {
            return self::$_user;
        }

        self::$_user = null;

        $userName = Yii::$app->request->get(self::USER_PARAM, null);
        if ($userName) {
            $user = User::findUserForApi($userName);
            if ($user) {
                self::$_user = $user;
            }
        }

        return self::$_user;
    }

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => 'yii\filters\auth\QueryParamAuth',
                'user' => 'customerApi',
                'optional' => ['*'],
                'tokenParam' => '_token',
            ],
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'user' => 'customerApi',
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                },
                'rules' => [
                    [
                        'actions' => ['constant', 'search', 'product', 'info',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }


    public function actionInfo()
    {
        return [];
    }
}
