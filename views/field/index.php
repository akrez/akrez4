<?php

use app\components\Alert;
use app\models\Field;
use yii\grid\GridViewAsset;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'Fields') . ': ' . $parentModel->title;

GridViewAsset::register($this);

$sort = $dataProvider->sort;
$modelClass = new Field();

$this->registerCss("
.table th {
    vertical-align: top !important;
    white-space: nowrap;
}
.table td {
    vertical-align: middle !important;
}
.p-absolute {
    position: relative;
}
");
$this->registerJs("
function applyFilter() { 
$('#table').yiiGridView(" . json_encode([
    'filterUrl' => Url::current(),
    'filterSelector' => '#table-filters input, #table-filters select',
    'filterOnFocusOut' => true,
]) . ");
}
$(document).on('click','.toggler',function() {

    var btn = $(this);

    var isHidden = $(btn.attr('toggle')).is(':hidden');    

    $('.toggler[toggle]').each(function(i) {
        var toggleBtn = $(this);
        $(toggleBtn.attr('toggle')).hide();
        toggleBtn.addClass('btn-default');
        toggleBtn.removeClass('btn-warning');
    });

    if(isHidden) {
        $(btn.attr('toggle')).show();
        btn.addClass('btn-warning');
        btn.removeClass('btn-default');
    }

});
$(document).on('pjax:beforeSend', function(xhr, options) {
    $('.ajax-splash-show').css('display','inline-block');
    $('.ajax-splash-hide').css('display','none');
});
$(document).on('pjax:complete', function(xhr, textStatus, options) {
    $('.ajax-splash-show').css('display','none');
    $('.ajax-splash-hide').css('display','inline-block');
    applyFilter();
});
applyFilter();
", View::POS_END);
$colspan = 7;
?>
<h3 class="pb20"><?= Html::encode($this->title) ?></h3>
<?php
Pjax::begin([
    'timeout' => false,
    'enablePushState' => false,
]);
?>
<div class="row">
    <div class="col-sm-12">
        <?= Alert::widget() ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary" style="position: relative;">
            <div class="ajax-splash-show splash-style"></div>
            <div class="panel-heading"><?= Yii::t('app', 'Fields') ?></div>
            <table id="table" class="table table-bordered table-condensed">
                <thead>
                    <tr class="info">
                        <th><?= $sort->link('title', ['label' => $modelClass->getAttributeLabel('title')]) ?></th>
                        <th><?= $sort->link('in_summary', ['label' => $modelClass->getAttributeLabel('in_summary')]) ?></th>
                        <th><?= $sort->link('seq', ['label' => $modelClass->getAttributeLabel('seq')]) ?></th>
                        <th><?= $sort->link('unit', ['label' => $modelClass->getAttributeLabel('unit')]) ?></th>
                        <th></th>
                    </tr>
                    <tr id="table-filters" class="info filters">
                        <th><?= Html::activeInput('text', $searchModel, 'title', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeInput('text', $searchModel, 'in_summary', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeInput('text', $searchModel, 'seq', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeInput('text', $searchModel, 'unit', ['class' => 'form-control']) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dataProvider->getModels()) : ?>
                        <?php foreach ($dataProvider->getModels() as $dataProviderModelKey => $dataProviderModel) : ?>

                            <?php
                            $trCssClass = ($dataProviderModelKey % 2 == 0 ? 'active' : '');
                            $displayState = null;
                            if ($model && $model->id == $dataProviderModel->id) {
                                $displayState = $state;
                                $dataProviderModel = $model;
                            }
                            ?>

                            <tr class="active">
                                <td><?= HtmlPurifier::process($dataProviderModel->title) ?></td>
                                <td>
                                    <?php
                                    if ($dataProviderModel->in_summary) :
                                        echo '<span class="glyphicon glyphicon-ok text-success" aria-hidden="true"></span>';
                                    else :
                                        echo '<span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span>';
                                    endif;
                                    ?>
                                </td>
                                <td><?= HtmlPurifier::process($dataProviderModel->seq) ?></td>
                                <td><?= HtmlPurifier::process($dataProviderModel->unit) ?></td>
                                <td><?= Html::button(Yii::t('app', 'Update'), ['class' => 'btn btn-block toggler' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->id]) ?></td>
                            </tr>

                            <tr class="" style="<?= $displayState == 'update' ? '' : 'display: none;' ?>" id="<?= "row-update-" . $dataProviderModel->id ?>">
                                <td colspan="<?= $colspan ?>" class="p-absolute">
                                    <?=
                                    $this->render('_form', [
                                        'model' => $dataProviderModel,
                                        'parentModel' => $parentModel,
                                        'autoCompleteSource' => $autoCompleteSource,
                                    ])
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="<?= $colspan ?>">
                                <?= Yii::t('yii', 'No results found.') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr class="success">
                        <td colspan="<?= $colspan ?>">
                            <?php
                            $form = ActiveForm::begin([
                                'options' => ['data-pjax' => true],
                                'action' => Url::current(['field/index', 'state' => 'batchSave', 'id' => null]),
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
                                    <?=
                                    $form->field($textAreaModel, 'values')->textarea([
                                        'rows' => count(explode("\n", $textAreaModel->values))
                                    ])->hint('هر ویژگی‌ را در یک خط بنویسید.')
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <?= Html::submitButton(' <span class="glyphicon glyphicon-plus-sign"></span> ' . Yii::t('app', 'Add Multiple'), ['class' => 'btn btn-block btn-social btn-success']); ?>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>