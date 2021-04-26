<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$mobile = Html::encode(Yii::$app->request->get('mobile'));
$verifyToken = Html::encode(Yii::$app->request->get('verify_token'));
?>

<div class="row">
    <div class="col-sm-4 col-xs-12">
        <img src="<?= Yii::getAlias('@web/image/verify.svg') ?>">
    </div>
    <div class="col-sm-4 col-xs-12">
        <h3 style="margin-bottom: 20px;"><?= Yii::t('app', 'Verify') ?></h3>
        <?php
        $form = ActiveForm::begin([
            'id' => 'login-form',
            'fieldConfig' => [
                'template' => '<div class="input-group">{label}{input}</div>{error}',
                'labelOptions' => [
                    'class' => 'input-group-addon',
                ],
            ]
        ]);
        ?>
        <?= $form->field($model, 'mobile')->textInput($mobile ? ['value' => $mobile, 'readonly' => true] : []) ?>
        <?= $form->field($model, 'verify_token')->textInput($verifyToken ? ['value' => $verifyToken, 'readonly' => true] : []) ?>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" name="login-button" style="float: right;"> <?= Yii::t('app', 'Verify') ?> </button>
        </div>
        <div class="form-group">
            <a type="button" class="btn btn-default" style="margin-top: 20px;float: right;" href="<?= Url::to(['site/verify-request', 'mobile' => $model->mobile]) ?>"><?= Yii::t('app', 'VerifyRequest') ?></a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-sm-4 col-xs-12">
    </div>
</div>