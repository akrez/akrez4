<?php

namespace app\components;

use Exception;
use Yii;
use yii\base\Component;

class Email extends Component
{
    public const EMAIL_SUPPORT = 'support@akrezing.ir';

    private static function send($from, $to, $subject, $view, $params)
    {
        if ($from === null) {
            $from = [self::EMAIL_SUPPORT => APP_NAME];
        }

        try {
            return Yii::$app->mailer
                            ->compose($view, $params)
                            ->setFrom($from)
                            ->setTo($to)
                            ->setSubject($subject)
                            ->send();
        } catch (Exception $e) {
        }
        return false;
    }

    public static function verifyRequest($user)
    {
        $title = Yii::t('app', 'VerifyRequest');
        return self::send(null, $user->email, $title, 'verifyRequest', [
                    '_title' => $title,
                    'user' => $user,
        ]);
    }

    public static function resetPasswordRequest($user)
    {
        $title = Yii::t('app', 'ResetPasswordRequest');
        return self::send(null, $user->email, $title, 'resetPasswordRequest', [
                    '_title' => $title,
                    'user' => $user,
        ]);
    }

    public static function customerResetPasswordRequest($customer, $blog)
    {
        $title = Yii::t('app', 'ResetPasswordRequest');
        return self::send([self::EMAIL_SUPPORT => $blog->title], $customer->email, $title, 'customerResetPasswordRequest', [
                    '_title' => $title,
                    'customer' => $customer,
                    'blog' => $blog,
        ]);
    }
}
