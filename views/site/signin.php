<?php

use app\models\Gallery;
use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<div class="row">
    <div class="col-sm-4 col-xs-12">
        <img src="<?= Gallery::getImageUrl(Gallery::TYPE_STORY ,'signin') ?>">
    </div>
    <div class="col-sm-4 col-xs-12">
        <h3 style="margin-bottom: 20px;"><?= Yii::t('app', 'Signin') ?></h3>
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
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'captcha', ['template' => '{input}<small>{hint}</small>{error}'])->widget(Captcha::class, [
            'template' => '{image}<div class="input-group">' . Html::activeLabel($model, 'captcha', ['class' => "input-group-addon"]) . '{input}</div>',
        ])->hint('در صورت ناخوانا بودن عکس، روی آن کلیک کنید.') ?>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" style="float: right;"> <?= Yii::t('app', 'Signin') ?> </button>
        </div>
        <div class="form-group">
            <a type="button" class="btn btn-default" style="margin-top: 20px;float: right;" href="<?= Url::to(['site/reset-password-request']) ?>"><?= Yii::t('app', 'ResetPasswordRequest') ?></a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>