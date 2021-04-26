<?php

use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<div class="row">
    <div class="col-sm-4 col-xs-12">
        <img src="<?= Yii::getAlias('@web/image/signup.svg') ?>">
    </div>
    <div class="col-sm-4 col-xs-12">
        <h3 style="margin-bottom: 20px;"><?= Yii::t('app', 'Signup') ?></h3>
        <?php
        $form = ActiveForm::begin([
            'id' => 'login-form',
            'fieldConfig' => [
                'template' => '<div class="input-group">{label}{input}</div><small>{hint}</small>{error}',
                'labelOptions' => [
                    'class' => 'input-group-addon',
                ],
            ]
        ]);
        ?>
        <?= $form->field($model, 'name')->textInput(['placeholder' => 'saipa'])->hint('برای استفاده بعنوان شناسه فروشگاه شما و همینطور در آدرس سایت استفاده میشود. فقط از حروف کوچک انگلیسی بدون فاصله استفاده کنید.') ?>
        <?= $form->field($model, 'title')->textInput(['placeholder' => 'سایپا']) ?>
        <?= $form->field($model, 'mobile')->textInput() ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'captcha', ['template' => '{input}<small>{hint}</small>{error}'])->widget(Captcha::class, [
            'template' => '{image}<div class="input-group">' . Html::activeLabel($model, 'captcha', ['class' => "input-group-addon"]) . '{input}</div>',
        ])->hint('در صورت ناخوانا بودن عکس، روی آن کلیک کنید.') ?>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" style="float: right;"> <?= Yii::t('app', 'Signup') ?> </button>
        </div>
        <div class="form-group">
            <a type="button" class="btn btn-default" style="margin-top: 20px;float: right;" href="<?= Url::to(['site/verify']) ?>"><?= Yii::t('app', 'Verify') ?></a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>