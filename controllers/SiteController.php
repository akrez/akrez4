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

class SiteController extends Controller
{
    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['error', 'index', 'captcha'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['?', '@'],
            ],
            [
                'actions' => ['signin', 'signup', 'reset-password-request', 'reset-password', 'verify', 'verify-request'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['?'],
            ],
            [
                'actions' => ['signout'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ],
        ]);
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'app\components\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSignin()
    {
        try {
            $signin = new Blog(['scenario' => 'signin']);
            if ($signin->load(Yii::$app->request->post()) && $signin->validate()) {
                Yii::$app->user->login($signin->getBlog(), 86400);
                return $this->goBack();
            }
            return $this->render('signin', ['model' => $signin]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionSignout()
    {
        try {
            $signout = Yii::$app->user->getIdentity();
            $signout->setAuthKey();
            if ($signout->save(false)) {
                Yii::$app->user->logout();
            }
            return $this->goHome();
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionSignup()
    {
        Blog::deleteUnverifiedTimeoutedBlog();
        try {
            $signup = new Blog(['scenario' => 'signup']);
            if ($signup->load(\Yii::$app->request->post())) {
                $signup->status = Status::STATUS_UNVERIFIED;
                $signup->setAuthKey();
                $signup->setVerifyToken();
                $signup->setDefaultLanguage();
                $signup->setPasswordHash($signup->password);
                if ($signup->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertSignupSuccessfull'));
                    return $this->redirect(['site/verify-request', 'mobile' => $signup->mobile]);
                }
            }
            return $this->render('signup', ['model' => $signup]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionResetPasswordRequest($mobile = '')
    {
        try {
            $resetPasswordRequest = new Blog(['scenario' => 'resetPasswordRequest']);
            $resetPasswordRequest->mobile = $mobile;
            if (($resetPasswordRequest->mobile || $resetPasswordRequest->load(\Yii::$app->request->post())) && $resetPasswordRequest->validate()) {
                $blog = $resetPasswordRequest->getBlog();
                $blog->setResetToken();
                if ($blog->save(false) && Sms::resetPasswordRequest($blog)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertResetPasswordRequestSuccessfull'));
                    return $this->redirect(['site/reset-password', 'mobile' => $blog->mobile]);
                }
            }
            return $this->render('reset-password-request', ['model' => $resetPasswordRequest]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionResetPassword($mobile = '')
    {
        try {
            $resetPassword = new Blog(['scenario' => 'resetPassword']);
            $resetPassword->mobile = $mobile;
            if ($resetPassword->load(\Yii::$app->request->post()) && $resetPassword->validate()) {
                $blog = $resetPassword->getBlog();
                $blog->setResetToken(true);
                $blog->setPasswordHash($resetPassword->password);
                if ($blog->save(false)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertResetPasswordSuccessfull'));
                    return $this->redirect(['site/index']);
                }
            }
            return $this->render('reset-password', ['model' => $resetPassword]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionVerifyRequest($mobile = '')
    {
        Blog::deleteUnverifiedTimeoutedBlog();
        try {
            $verifyRequest = new Blog(['scenario' => 'verifyRequest']);
            $verifyRequest->mobile = $mobile;
            if (($verifyRequest->mobile || $verifyRequest->load(\Yii::$app->request->post())) && $verifyRequest->validate()) {
                $blog = $verifyRequest->getBlog();
                $blog->setVerifyToken();
                if ($blog->save(false) && Sms::verifyRequest($blog)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertVerifyRequestSuccessfull'));
                    return $this->redirect(['site/verify', 'mobile' => $blog->mobile]);
                }
            }
            return $this->render('verify-request', ['model' => $verifyRequest]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionVerify($mobile = '')
    {
        Blog::deleteUnverifiedTimeoutedBlog();
        try {
            $verify = new Blog(['scenario' => 'verify']);
            $verify->mobile = $mobile;
            if ($verify->load(\Yii::$app->request->post()) && $verify->validate()) {
                $blog = $verify->getBlog();
                $blog->setVerifyToken(true);
                $blog->status = Status::STATUS_ACTIVE;
                if ($blog->save(false)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertVerifySuccessfull'));
                    return $this->redirect(['site/index']);
                }
            }
            return $this->render('verify', ['model' => $verify]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }
}
