<?php

use app\models\Blog;
use app\models\Language;
use app\models\Status;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<?php
$form = ActiveForm::begin([
    'options' => ['data-pjax' => true],
    'action' => Url::current(['blog/profile', 'id' => $model->id, 'state' => ($model->isNewRecord ? 'save' : 'update'),]),
    'fieldConfig' => [
        'template' => '<div class="input-group">{label}{input}</div>{hint}{error}',
        'labelOptions' => [
            'class' => 'input-group-addon',
        ],
    ]
]);
?>

<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'title')->textInput() ?>
    </div>
    <div class="col-sm-6">
        <?= $form->field($model, 'slug')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'status')->dropDownList(Blog::validStatuses()) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-9">
        <?= $form->field($model, 'des')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'language')->dropDownList(Language::getList()) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'instagram')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'whatsapp')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'facebook')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'twitter')->textInput() ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'telegram')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'telegram_user')->textInput() ?>
    </div>
    <div class="col-sm-6">
        <?= $form->field($model, 'telegram_bot_token')->textInput() ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'email')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'phone')->textInput() ?>
    </div>
    <div class="col-sm-6">
        <?= $form->field($model, 'address')->textInput() ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <?= Html::submitButton($model->isNewRecord ? ' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create') : ' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]); ?>
    </div>
</div>

<?php ActiveForm::end(); ?>