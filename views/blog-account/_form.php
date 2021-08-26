<?php

use app\models\BlogAccount;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?php
$form = ActiveForm::begin([
    'options' => ['data-pjax' => true],
    'action' => Url::current(['blog-account/index', 'id' => $model->id, 'state' => ($model->isNewRecord ? 'create' : 'update'),]),
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
        <?= $form->field($model, 'name')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'identity_type')->dropDownList(BlogAccount::getTypeList()) ?>
    </div>
    <div class="col-sm-6">
        <?= $form->field($model, 'identity')->textInput() ?>
    </div>
</div>
<div class="row mt10">
    <div class="col-sm-3">
        <?= Html::submitButton($model->isNewRecord ? ' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create') : ' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>