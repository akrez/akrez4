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
                <tbody>
                    <tr class="success">
                        <td >
                            <?= $this->render('_form', ['model' => $newModel]) ?>
                        </td>
                    </tr>
                    <?php
                    foreach ($dataProvider->getModels() as $dataProviderModelKey => $dataProviderModel) :
                        if ($model && $model->id == $dataProviderModel->id && !$model->isNewRecord) {
                            $dataProviderModel = $model;
                        }
                        if (isset($colorRawList[$dataProviderModel->code])) {
                            unset($colorRawList[$dataProviderModel->code]);
                        }
                    ?>
                        <tr style="display: table-row;">
                            <td >
                                <?= $this->render('_form', ['model' => $dataProviderModel]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php
                    foreach ($colorRawList as $colorRawCode => $colorRawTitle) :
                        if ($model && $model->code == $colorRawCode && $model->isNewRecord) {
                            $dataProviderModel = $model;
                        } else {
                            $dataProviderModel = new Color();
                            $dataProviderModel->title = $colorRawTitle;
                            $dataProviderModel->code = $colorRawCode;
                        }
                    ?>
                        <tr class="success" style="display: table-row;">
                            <td >
                                <?= $this->render('_form', ['model' => $dataProviderModel]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>