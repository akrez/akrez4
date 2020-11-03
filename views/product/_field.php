<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<?php
$form = ActiveForm::begin([
            'options' => ['data-pjax' => true],
            'action' => Url::current(['product/index', 'id' => $model->id, 'state' => 'saveFields',]),
            'fieldConfig' => [
                'template' => '<div class="input-group">{label}{input}</div>{hint}{error}',
                'labelOptions' => [
                    'class' => 'input-group-addon',
                ],
            ]
        ]);
?>
<div class="row">
    <div class="col-sm-12">
        <?= $form->field($textAreaModel, 'values')->textarea(['rows' => count($textAreaModel->explodeLines())]) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <?= Html::submitButton(' <span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social btn-primary']); ?>
    </div>
</div>

<?php ActiveForm::end(); ?>