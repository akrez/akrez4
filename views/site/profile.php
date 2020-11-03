<?php

use app\models\Gallery;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$model->password = '';
$this->registerJs("
    $(document).on('change', '#gallery-subbutton', function () {
        $(this).closest('form').submit();
    });
");
?>

<div class="row">
    <?php
    $form = ActiveForm::begin([
                'options' => [
                    'enctype' => 'multipart/form-data',
                ],
                'fieldConfig' => [
                    'template' => '<div class="input-group">{label}{input}</div><small>{hint}</small>{error}',
                    'labelOptions' => [
                        'class' => 'input-group-addon',
                    ],
                ]
    ]);
    ?>
    <div class="col-sm-2 pb20">
        <img class="img img-responsive img-rounded" src="<?= empty($model->logo) ? Yii::getAlias('@web/image/logo.png') : Gallery::getImageUrl(Gallery::TYPE_LOGO, $model->logo) ?>">
        <a class="btn btn-success btn-block mt4 mb0" href="javascript:void(0);" onclick="$('#gallery-subbutton').click()">
            <span class="glyphicon glyphicon-refresh" style="border: none;display: none"></span>
            <span><?= Yii::t('app', 'UploadNewImage') ?></span>
        </a>
        <?= $form->errorSummary($model, ['class' => 'mt4']) ?>
        <?= $form->field($model, 'image', ['options' => ['style' => 'display: none']])->fileInput(['id' => 'gallery-subbutton']); ?>
    </div>
    <div class="col-sm-10">
        <h2 class="mt10 mb20"><?= Yii::t('app', 'Profile') ?></h2>
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, 'title')->textInput() ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, 'slug')->textInput() ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($model, 'twitter')->textInput() ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'facebook')->textInput() ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'telegram')->textInput() ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'instagram')->textInput() ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($model, 'mobile')->textInput() ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'phone')->textInput() ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, 'address')->textInput() ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <?= $form->field($model, 'des')->textarea() ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <?= $form->field($model, 'password')->passwordInput()->hint('برای عدم تغییر رمز عبور خالی بگذارید.') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block" style="float: right;"> <?= Yii::t('app', 'Update') ?> </button>
                </div>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>
