<?php

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
        <?= $form->field($model, 'email')->textInput() ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" style="float: right;"> <?= Yii::t('app', 'Signup') ?> </button>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
