<?php

use app\components\Alert;
use app\models\Category;
use app\models\Page;
use yii\grid\GridViewAsset;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'Categories');

GridViewAsset::register($this);

$sort = $dataProvider->sort;
$modelClass = new Category();

$this->registerCss("
.table th {
    vertical-align: top !important;
}
.table td {
    vertical-align: middle !important;
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
    'id' => "category-pjax",
    'timeout' => false,
    'enablePushState' => false,
]);

$this->registerJs("
$('#table').yiiGridView(" . json_encode([
    'filterUrl' => Url::current(['CategorySearch' => null,]),
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
            <div class="panel-heading"><?= Html::encode($this->title) ?></div>
            <table id="table" class="table table-bordered table-striped">
                <thead>
                    <tr class="info">
                        <th><?= $sort->link('title', ['label' => $modelClass->getAttributeLabel('title')]) ?></th>
                        <th><?= $sort->link('status', ['label' => $modelClass->getAttributeLabel('status')]) ?></th>
                        <th><?= $modelClass->getAttributeLabel('des') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr id="table-filters" class="info">
                        <th><?= Html::activeInput('text', $searchModel, 'title', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeDropDownList($searchModel, 'status', Category::validStatuses(), ['class' => 'form-control', 'prompt' => '']) ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
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
                                    <?= HtmlPurifier::process($dataProviderModel->title) ?>
                                </td>
                                <td>
                                    <?= Yii::$app->formatter->asStatus($dataProviderModel->status) ?>
                                </td>
                                <td>
                                    <?= HtmlPurifier::process($dataProviderModel->des) ?>
                                </td>
                                <td>
                                    <?= Html::button(Yii::t('app', 'Update'), ['class' => 'btn btn-block' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->id]) ?>
                                </td>
                                <td>
                                    <?=
                                    Html::a(' <span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Fields'), Url::to(['field/index', 'parent_id' => $dataProviderModel->id]), [
                                        'class' => 'btn btn-default btn-block btn-social',
                                        'data-pjax' => '0',
                                    ]);
                                    ?>
                                </td>
                                <td>
                                    <?=
                                    Html::a(' <span class="glyphicon glyphicon-file"></span> ' . Yii::t('app', 'Page'), Url::to([0 => 'page/index', 'entity' => Page::ENTITY_CATEGORY, 'entity_id' => $dataProviderModel->id]), [
                                        'class' => 'btn btn-default btn-block btn-social',
                                        'data-pjax' => '0',
                                    ]);
                                    ?>
                                </td>
                                <td>
                                    <?=
                                    Html::a(' <span class="glyphicon glyphicon-grain"></span> ' . Yii::t('app', 'Products'), Url::to([0 => 'product/index', 'parent_id' => $dataProviderModel->id]), [
                                        'class' => 'btn btn-default btn-block btn-social',
                                        'data-pjax' => '0',
                                    ]);
                                    ?>
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
                                <td colspan="7">
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
                        <td colspan="7">
                            <?php
                            $form = ActiveForm::begin([
                                'options' => ['data-pjax' => true],
                                'action' => Url::current(['category/index', 'state' => 'batchSave']),
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
                                        'rows' => count($textAreaModel->explodeLines())
                                    ])->hint('عنوان هر دسته‌بندی را در یک خط بنویسید.')
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