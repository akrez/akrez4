<?php

namespace app\controllers;

use app\models\Telegram;
use app\models\Product;
use Yii;

class TelegramController extends Controller
{

    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['send-product-to-channel',],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ],
        ]);
    }

    public function actionSendProductToChannel($product_id, $package_id = '')
    {
        $product = Product::blogValidQuery($product_id)->one();
        $response = [];
        if ($product) {
            $response = Telegram::sendProductToChannel(Yii::$app->user->getIdentity(), $product, $package_id);
        }
        return $this->asJson($response);
    }
}
