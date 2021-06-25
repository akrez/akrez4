<?php

use app\components\Alert;
use app\models\Color;
use app\models\Package;
use yii\grid\GridViewAsset;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'Packages') . ': ' . $parentModel->title;

GridViewAsset::register($this);

$sort = $dataProvider->sort;
$modelClass = new Package();

$format = <<< SCRIPT
function format(data, container) {
    return ' <span style="background-color: ' + data.id + ';">⠀⠀</span> ' + data.text;
}
SCRIPT;
$this->registerJs($format, View::POS_HEAD);

$this->registerCss("
.table th {
    vertical-align: top !important;
}
.table td {
    vertical-align: middle !important;
}
");

$this->registerCss("
.color-class {
    border: solid 1px #333333;
    border-radius: 12px;
    margin-left: 4px;
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
$(document).on('click', '.sendTelegram', function() {
    var url = $(this).attr('data-url');
    $.get(url);
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
    'id' => "package-pjax",
    'timeout' => false,
    'enablePushState' => false,
]);

$this->registerJs("
$('#table').yiiGridView(" . json_encode([
    'filterUrl' => Url::current(['PackageSearch' => null,]),
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
            <div class="panel-heading"><?= Yii::t('app', 'Packages') ?></div>
            <table id="table" class="table table-bordered table-striped">
                <thead>
                    <tr class="info">
                        <th><?= $modelClass->getAttributeLabel('guaranty') ?></th>
                        <th><?= $modelClass->getAttributeLabel('color_code') ?></th>
                        <th><?= $modelClass->getAttributeLabel('des') ?></th>
                        <th><?= $sort->link('status', ['label' => $modelClass->getAttributeLabel('status')]) ?></th>
                        <th><?= $sort->link('updated_at', ['label' => $modelClass->getAttributeLabel('updated_at')]) ?></th>
                        <th><?= $sort->link('price', ['label' => $modelClass->getAttributeLabel('price')]) ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr id="table-filters" class="info">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><?= Html::activeDropDownList($searchModel, 'status', Package::validStatuses(), ['class' => 'form-control', 'prompt' => '']) ?></th>
                        <th></th>
                        <th><?= Html::activeInput('text', $searchModel, 'price', ['class' => 'form-control']) ?></th>
                        <th></th>
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
                                <td>
                                    <?= HtmlPurifier::process($dataProviderModel->guaranty) ?>
                                </td>
                                <td>
                                    <?php if ($dataProviderModel->color_code) : ?>
                                        <span class="color-class" style="background-color: <?= $dataProviderModel->color_code ?>;">⠀⠀</span> <?= Color::getLabel($dataProviderModel->color_code) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= HtmlPurifier::process($dataProviderModel->des) ?>
                                </td>
                                <td>
                                    <?= Yii::$app->formatter->asStatus($dataProviderModel->status) ?>
                                </td>
                                <td>
                                    <?= Yii::$app->formatter->asDatetimefa($dataProviderModel->updated_at) ?>
                                </td>
                                <td>
                                    <?= Yii::$app->formatter->asPrice($dataProviderModel->price) ?>
                                </td>
                                <td>
                                    <?= Html::button(Yii::t('app', 'Update'), ['class' => 'btn btn-block' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->id]) ?>
                                </td>
                                <td>
                                    <?= Html::button('<span class="glyphicon glyphicon-send"></span>' . Yii::t('app', 'Telegram'), [
                                        'class' => 'btn btn-info btn-block btn-social sendTelegram',
                                        'data-url' => Url::to(['telegram/send-product-to-channel', 'product_id' => $dataProviderModel->product_id, 'package_id' => $dataProviderModel->id]),
                                    ]) ?>
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
                        <td colspan="8">
                            <?= $this->render('_form', ['model' => $newModel]) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>

<div class="row pb20 pt20">
    <div class="col-sm-2">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), Url::to(['product/index', 'parent_id' => $parentModel->category_id]), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>