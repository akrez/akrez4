<?php

use app\models\Color;
use app\models\Package;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

?>
<?php
$form = ActiveForm::begin([
    'options' => ['data-pjax' => true],
    'action' => Url::current(['package/index', 'id' => $model->id, 'state' => ($model->isNewRecord ? 'create' : 'update'),]),
    'fieldConfig' => [
        'template' => '<div class="input-group">{label}{input}</div>{hint}{error}',
        'labelOptions' => [
            'class' => 'input-group-addon',
        ],
    ]
]);
$colorTagId = Html::getInputId($model, 'color') . '-' . $model->id;
?>



<div class="row">
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'guaranty')->textInput() ?>
    </div>
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'price')->textInput() ?>
    </div>
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'status')->dropDownList(Package::validStatuses()) ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-4">
        <?=
        $form->field($model, 'color')->widget(Select2::class, [
            'data' => Color::getList(),
            'options' => [
                'placeholder' => '',
                'id' => $colorTagId,
                'dir' => 'rtl',
            ],
            'pluginOptions' => [
                'templateResult' => new JsExpression('format'),
                'templateSelection' => new JsExpression('format'),
                'escapeMarkup' => new JsExpression("function(m) { return m; }"),
                'allowClear' => true
            ],
        ]);
        ?>

    </div>
    <div class="col-xs-12 col-sm-8">
        <?= $form->field($model, 'des')->textInput(['maxlength' => true]) ?>
    </div>
</div>



<div class="row">
    <div class="col-sm-3">
        <?= Html::submitButton($model->isNewRecord ? ' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create') : ' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]); ?>
    </div>
    <div class="col-sm-3">
        <?php
        if (!$model->isNewRecord) :
            echo Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), Url::to([0 => 'package/index', 'state' => 'remove', 'id' => $model->id, 'parent_id' => $model->product_id]), [
                'class' => 'btn btn-danger btn-block btn-social',
                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            ]);
        endif;
        ?>
    </div>
</div>
<?php ActiveForm::end(); ?>