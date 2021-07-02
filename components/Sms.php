<?php

namespace app\components;

use app\models\Customer;
use Exception;
use Yii;
use yii\base\Component;

class Sms extends Component
{

    public static function send($to, $text)
    {
        try {
            $api = Yii::$app->Melipayamak->Api();
            $sms = $api->sms();
            $response = $sms->send($to, '50004001111553', $text);
            $json = json_decode($response);
            return $json->Value; //RecId or Error Number 
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function verifyRequest($blog)
    {
        $title = Yii::t('app', 'VerifyRequest');
        return self::send($blog->mobile, Yii::$app->name . "\n" . $title . ': ' . $blog->verify_token);
    }

    public static function resetPasswordRequest($blog)
    {
        $title = Yii::t('app', 'ResetPasswordRequest');
        return self::send($blog->mobile, Yii::$app->name . "\n" . $title . ': ' . $blog->reset_token);
    }

    public static function customerVerifyRequest($customer, $blog)
    {
        $title = Yii::t('app', 'VerifyRequest');
        return self::send($blog->mobile, $blog->title . "\n" . $title . ': ' . $customer->verify_token);
    }

    public static function customerResetPasswordRequest($customer, $blog)
    {
        $title = Yii::t('app', 'ResetPasswordRequest');
        return self::send($blog->mobile, $blog->title . "\n" . $title . ': ' . $customer->reset_token);
    }
}
