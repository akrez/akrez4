<?php

use kartik\color\ColorInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?php
$form = ActiveForm::begin([
    'options' => ['data-pjax' => true],
    'action' => Url::current(['color/index', 'id' => $model->id, 'state' => ($model->isNewRecord ? 'create' : 'update'),]),
    'fieldConfig' => [
        'template' => '<div class="input-group">{label}{input}</div>{hint}{error}',
        'labelOptions' => [
            'class' => 'input-group-addon',
        ],
    ]
]);
$codeTagId = Html::getInputId($model, 'code') . '-' . $model->id;
?>



<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'title')->textInput() ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'code', [])->widget(ColorInput::class, [
            'options' => [
                'id' => $codeTagId,
                'dir' => 'rtl',
            ],
        ])->label(false); ?>
    </div>
    <div class="col-sm-3">
        <?= Html::submitButton($model->isNewRecord ? ' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create') : ' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]); ?>
    </div>
    <div class="col-sm-3">
        <?php
        if (!$model->isNewRecord) :
            echo Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), Url::to([0 => 'color/index', 'state' => 'remove', 'id' => $model->id]), [
                'class' => 'btn btn-danger btn-block btn-social',
                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            ]);
        endif;
        ?>
    </div>
</div>
<?php ActiveForm::end(); ?>