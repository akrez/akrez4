<?php

namespace app\controllers;

use app\components\Email;
use app\models\Gallery;
use app\models\Status;
use app\models\Blog;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['error', 'index'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['?', '@'],
            ],
            [
                'actions' => ['signin', 'signup', 'reset-password-request', 'reset-password'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['?'],
            ],
            [
                'actions' => ['profile', 'signout'],
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
        try {
            $signup = new Blog(['scenario' => 'signup']);
            if ($signup->load(\Yii::$app->request->post())) {
                $signup->status = Status::STATUS_UNVERIFIED;
                $signup->setAuthKey();
                $signup->setVerifyToken();
                $signup->setPasswordHash($signup->password);
                if ($signup->save()) {
                    Email::verifyRequest($signup);
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertSignupSuccessfull'));
                    return $this->goBack();
                }
            }
            return $this->render('signup', ['model' => $signup]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionProfile()
    {
        try {
            $profile = \Yii::$app->user->getIdentity();
            $profile->scenario = 'profile';
            if ($profile->image = UploadedFile::getInstance($profile, 'image')) {
                $logo = $profile->logo;
                $gallery = Gallery::upload($profile->image->tempName, Gallery::TYPE_LOGO);
                if ($gallery->hasErrors()) {
                    $profile->addErrors(['image' => $gallery->getErrorSummary(true)]);
                } else {
                    $profile->logo = $gallery->name;
                    if ($profile->save()) {
                        if ($oldGallery = Gallery::findOne($logo)) {
                            $oldGallery->delete();
                            Yii::$app->session->setFlash('success', Yii::t('app', 'alertUpdateSuccessfull'));
                            return $this->refresh();
                        }
                    }
                }
            } elseif ($profile->load(\Yii::$app->request->post())) {
                if ($profile->password) {
                    $profile->setPasswordHash($profile->password);
                }
                if ($profile->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertUpdateSuccessfull'));
                    return $this->refresh();
                }
            }
            return $this->render('profile', ['model' => $profile]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionResetPasswordRequest()
    {
        try {
            $resetPasswordRequest = new Blog(['scenario' => 'resetPasswordRequest']);
            if ($resetPasswordRequest->load(\Yii::$app->request->post()) && $resetPasswordRequest->validate()) {
                $blog = $resetPasswordRequest->getBlog();
                $blog->setResetToken();
                if ($blog->save(false) && Email::resetPasswordRequest($blog)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertResetPasswordRequestSuccessfull'));
                    return $this->redirect(['site/index']);
                }
            }
            return $this->render('reset-password-request', ['model' => $resetPasswordRequest]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionResetPassword()
    {
        try {
            $resetPassword = new Blog(['scenario' => 'resetPassword']);
            if ($resetPassword->load(\Yii::$app->request->post()) && $resetPassword->validate()) {
                $blog = $resetPassword->getBlog();
                $blog->setResetToken(true);
                $blog->status = Status::STATUS_ACTIVE;
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
}
