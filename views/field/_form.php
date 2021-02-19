<?php

use app\models\Field;
use app\models\FieldList;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\JuiAsset;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Field */
/* @var $form ActiveForm */

JuiAsset::register($this);
$this->registerJs("
    $('.autocomplete').autocomplete(" . json_encode([
            'source' => $autoCompleteSource,
            'minLength' => 0,
        ]) . ").on('focus', function() { $(this).keydown(); });
", View::POS_READY);
$this->registerCss("
    .checkbox-list {
        padding-bottom: 4px;
        padding-left: 12px;
        padding-right: 12px;
        padding-top: 4px;
        border-bottom-left-radius: 4px;
        border-top-left-radius: 4px;
        border-bottom-right-radius: 0;
        border-top-right-radius: 0;
        border: 1px solid #ccc;
        transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
    }
    label {
        margin: 4px 0 4px 10px;
    }
");
?>

<div class="container-fluid pt15 pb15">

    <?php
    $form = ActiveForm::begin([
                'options' => ['data-pjax' => true],
                'action' => Url::current(['field/index', 'id' => $model->id, 'state' => ($model->isNewRecord ? 'save' : 'update'),]),
                'fieldConfig' => [
                    'template' => '<div class="input-group">{label}{input}</div>{hint}{error}',
                    'labelOptions' => [
                        'class' => 'input-group-addon',
                    ],
                ],
                'options' => [
                    'data-pjax' => true,
                ],
    ]);
    ?>

    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'title')->textInput(['class' => 'form-control autocomplete']) ?>
        </div>
        <div class="col-sm-3">
            <?=
            $form->field($model, 'in_summary')->widget(Select2::classname(), [
                'data' => [
                    1 => Yii::$app->formatter->booleanFormat[1],
                    0 => Yii::$app->formatter->booleanFormat[0],
                ],
                'hideSearch' => true,
                'options' => [
                    'placeholder' => '',
                    'id' => Html::getInputId($model, 'in_summary') . '-select2-' . $model->id,
                    'dir' => 'rtl',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
            ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'seq')->textInput(['type' => 'number']) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'unit')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <?php
            echo $form->field($model, 'widgets', [
            ])->checkboxList(FieldList::widgetsList(), [
                'class' => 'checkbox-list',
                'itemOptions' => [
                    'labelOptions' => [
                        'style' => 'margin-left: 10px;',
                    ],
                ],
            ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= Html::submitButton($model->isNewRecord ? ' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create') : ' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]); ?>
        </div>
        <div class="col-sm-3">
            <?php
            if (!$model->isNewRecord) :
                echo Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), Url::to([0 => 'field/index', 'state' => 'remove', 'parent_id' => $parentModel->id, 'id' => $model->id]), [
                    'class' => 'btn btn-danger btn-block btn-social',
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                ]);
            endif;
            ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>