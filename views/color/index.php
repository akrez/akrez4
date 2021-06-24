<?php

use app\components\Alert;
use app\models\Color;
use yii\grid\GridViewAsset;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'Colors');

GridViewAsset::register($this);

$sort = $dataProvider->sort;
$modelClass = new Color();

$this->registerCss("
.table th {
    vertical-align: top !important;
}
.table td {
    vertical-align: middle !important;
}
.font-family-monospace {
    font-family: monospace;
}
.color-class {
    border: solid 1px #333333;
    border-radius: 12px;
    margin-left: 4px;
}
.td-ltr {
    direction: ltr;
    text-align: right;
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
$(document).on('click','.btn[toggle]',function() {

    var btn = $(this);
    var isHidden = $(btn.attr('toggle')).is(':hidden');    

    $('.btn[toggle]').each(function(i) {
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
", View::POS_READY);
?>

<h3 class="pb20"><?= Html::encode($this->title) ?></h3>

<?php
Pjax::begin([
    'id' => "color-pjax",
    'timeout' => false,
    'enablePushState' => false,
]);

$this->registerJs("
$('#table').yiiGridView(" . json_encode([
    'filterUrl' => Url::current(['ColorSearch' => null,]),
    'filterSelector' => '#table-filters input, #table-filters select',
    'filterOnFocusOut' => true,
]) . ");
");
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
            <div class="panel-heading"><?= Yii::t('app', 'Colors') ?></div>
            <table id="table" class="table table-bordered table-striped">
                <thead>
                    <tr class="info">
                        <th><?= $modelClass->getAttributeLabel('title') ?></th>
                        <th><?= $modelClass->getAttributeLabel('code') ?></th>
                        <th></th>
                    </tr>
                    <tr id="table-filters" class="info">
                        <th><?= Html::activeInput('text', $searchModel, 'title', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeInput('text', $searchModel, 'code', ['class' => 'form-control']) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dataProvider->getModels()) { ?>
                        <?php
                        foreach ($dataProvider->getModels() as $dataProviderModelKey => $dataProviderModel) :
                            $displayState = '';
                            if ($model && $model->id == $dataProviderModel->id) {
                                $displayState = $state;
                                $dataProviderModel = $model;
                            }
                        ?>
                            <tr class="active">
                                <td class="td-ltr">
                                    <span class=""><?= HtmlPurifier::process($dataProviderModel->title) ?></span>
                                </td>
                                <td class="td-ltr">
                                    <span class="font-family-monospace"><?= $dataProviderModel->code ?></span>
                                    <span class="color-class" style="background-color: <?= $dataProviderModel->code ?>;">⠀⠀</span>
                                </td>
                                <td>
                                    <?= Html::button(Yii::t('app', 'Update'), ['class' => 'btn btn-block' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->id]) ?>
                                </td>
                            </tr>
                            <?php
                            $displayStyle = 'display: none;';
                            if ($model && $model->id == $dataProviderModel->id) {
                                $dataProviderModel = $model;
                                $displayStyle = 'display: table-row;';
                            }
                            ?>
                            <tr class="" style="<?= $displayStyle ?>" id="<?= "row-update-" . $dataProviderModel->id ?>">
                                <td colspan="8">
                                    <?= $this->render('_form', ['model' => $dataProviderModel]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php } else { ?>
                        <tr class="danger">
                            <td colspan="8">
                                <?= Yii::t('yii', 'No results found.') ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr class="success">
                        <td colspan="3">
                            <?= $this->render('_form', ['model' => $newModel]) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>