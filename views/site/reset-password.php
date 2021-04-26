<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$mobile = Html::encode(Yii::$app->request->get('mobile'));
$resetToken = Html::encode(Yii::$app->request->get('reset_token'));
?>

<div class="row">
    <div class="col-sm-4 col-xs-12">
        <img src="<?= Yii::getAlias('@web/image/reset-password.svg') ?>">
    </div>
    <div class="col-sm-4 col-xs-12">
        <h3 style="margin-bottom: 20px;"><?= Yii::t('app', 'ResetPassword') ?></h3>
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
        <?= $form->field($model, 'reset_token')->textInput($resetToken ? ['value' => $resetToken, 'readonly' => true] : []) ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" name="login-button" style="float: right;"> <?= Yii::t('app', 'ResetPassword') ?> </button>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-sm-4 col-xs-12">
    </div>
</div>