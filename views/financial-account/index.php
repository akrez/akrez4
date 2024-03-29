<?php

use app\components\Alert;
use app\models\FinancialAccount;
use yii\grid\GridViewAsset;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'Financial Account');

GridViewAsset::register($this);

$sort = $dataProvider->sort;
$modelClass = new FinancialAccount();

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
.blogAccount-class {
    border: solid 1px #333333;
    border-radius: 12px;
    margin-left: 4px;
}
.td-ltr {
    direction: ltr;
    text-align: right;
}
.input-group-html5 .input-group-addon + .form-control {
    border-width: 0.8px !important;
}
div.help-block:empty {
    margin: 0;
}
.form-group {
    margin-bottom: 0;
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
    'id' => "blogAccount-pjax",
    'timeout' => false,
    'enablePushState' => false,
]);

$this->registerJs("
$('#table').yiiGridView(" . json_encode([
    'filterUrl' => Url::current(['FinancialAccountSearch' => null,]),
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
            <div class="panel-heading"><?= Yii::t('app', 'Financial Account') ?></div>
            <table id="table" class="table table-bordered table-striped">
                <thead>
                    <tr class="info">
                        <th><?= $sort->link('name', ['label' => $modelClass->getAttributeLabel('name')]) ?></th>
                        <th><?= $sort->link('identity_type', ['label' => $modelClass->getAttributeLabel('identity_type')]) ?></th>
                        <th><?= $sort->link('identity', ['label' => $modelClass->getAttributeLabel('identity')]) ?></th>
                        <th></th>
                    </tr>
                    <tr id="table-filters" class="info">
                        <th><?= Html::activeInput('text', $searchModel, 'name', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeDropDownList($searchModel, 'identity_type', FinancialAccount::getTypeList(), ['class' => 'form-control', 'prompt' => '']) ?></th>
                        <th><?= Html::activeInput('text', $searchModel, 'identity', ['class' => 'form-control']) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dataProvider->getModels()) { ?>
                        <?php
                        foreach ($dataProvider->getModels() as $dataProviderModelKey => $dataProviderModel) :
                            $displayState = '';
                            $displayStyle = 'display: none;';
                            if ($model && $model->id == $dataProviderModel->id) {
                                $displayState = $state;
                                $displayStyle = 'display: table-row;';
                                $dataProviderModel = $model;
                            }
                        ?>
                            <tr class="active">
                                <td>
                                    <?= HtmlPurifier::process($dataProviderModel->name) ?>
                                </td>
                                <td>
                                    <?= FinancialAccount::getTypeLabel($dataProviderModel->identity_type) ?>
                                </td>
                                <td>
                                    <?= HtmlPurifier::process($dataProviderModel->identity) ?>
                                </td>
                                <td>
                                    <?= Html::button(Yii::t('app', 'Update'), ['class' => 'btn btn-block' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->id]) ?>
                                </td>
                            </tr>
                            <tr style="<?= $displayStyle ?>" id="<?= "row-update-" . $dataProviderModel->id ?>">
                                <td colspan="4">
                                    <?= $this->render('_form', ['model' => $dataProviderModel]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php } else { ?>
                        <tr class="danger">
                            <td colspan="7">
                                <?= Yii::t('yii', 'No results found.') ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr class="success">
                        <td colspan="4">
                            <?= $this->render('_form', ['model' => $newModel]) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>