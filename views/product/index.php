<?php

use app\components\Alert;
use app\models\Category;
use app\models\Gallery;
use app\models\Page;
use app\models\Product;
use app\models\TextArea;
use yii\grid\GridViewAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'Products') . ': ' . $parentModel->title;

GridViewAsset::register($this);

$sort = $dataProvider->sort;
$modelClass = new Category();

$this->registerCss("
.table th {
    vertical-align: top !important;
    white-space: nowrap;
}
.table td {
    vertical-align: middle !important;
}
.max-height-40 {
    max-height: 40px;
}    
");
$colspan = 11;
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
function galleryFormSubmit(input) {
    $(input).closest('form').submit();
}
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
", View::POS_END);
?>
<h3 class="pb20"><?= Html::encode($this->title) ?></h3>

<?php
Pjax::begin([
    'id' => "product-pjax",
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
            <div class="panel-heading"><?= Yii::t('app', 'Products') ?></div>
            <table id="table" class="table table-bordered table-condensed">
                <thead>
                    <tr class="info">
                        <th><?= $sort->link('updated_at', ['label' => $modelClass->getAttributeLabel('updated_at')]) ?></th>
                        <th><?= $modelClass->getAttributeLabel('image') ?></th>
                        <th><?= $sort->link('title', ['label' => $modelClass->getAttributeLabel('title')]) ?></th>
                        <th><?= $modelClass->getAttributeLabel('des') ?></th>
                        <th><?= $sort->link('status', ['label' => $modelClass->getAttributeLabel('status')]) ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr id="table-filters" class="info filters">
                        <th></th>
                        <th></th>
                        <th><?= Html::activeInput('text', $searchModel, 'title', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeInput('text', $searchModel, 'des', ['class' => 'form-control']) ?></th>
                        <th><?= Html::activeDropDownList($searchModel, 'status', Product::validStatuses(), ['class' => 'form-control', 'prompt' => '']) ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dataProvider->getModels()) : ?>
                        <?php foreach ($dataProvider->getModels() as $dataProviderModelKey => $dataProviderModel) : ?>
                            <?php
                            $trCssClass = ($dataProviderModelKey % 2 == 0 ? 'active' : '');
                            //
                            $displayState = null;
                            $isGalleryState = false;
                            //
                            $fieldsLine = [];
                            foreach (ArrayHelper::map($dataProviderModel->productFields, 'value', 'value', 'field') + $autoCompleteSource as $fieldKey => $fieldValues) {
                                $fieldsLine[] = $fieldKey . ' : ' . implode(' , ', (array) $fieldValues);
                            }
                            $dataProviderTextAreaModel = new TextArea();
                            $dataProviderTextAreaModel->setValues($fieldsLine);
                            //
                            if ($model && $model->id == $dataProviderModel->id) {
                                $displayState = $state;
                                if ($state == 'saveFields') {
                                    if ($textAreaFields->hasErrors()) {
                                        $dataProviderTextAreaModel = $textAreaFields;
                                    }
                                } else {
                                    $dataProviderModel = $model;
                                }
                                //
                                if (in_array($state, ['galleryDefault', 'galleryDelete', 'galleryUpload'])) {
                                    $isGalleryState = true;
                                }
                            }
                            ?>
                            <tr class="active">
                                <td><?= Yii::$app->formatter->asDatetimefa($dataProviderModel->updated_at) ?></td>
                                <td class="text-center">
                                    <?php if ($dataProviderModel->image) : ?>
                                        <img class="img max-height-40" src="<?= Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $dataProviderModel->image) ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?= HtmlPurifier::process($dataProviderModel->title) ?></td>
                                <td><?= HtmlPurifier::process($dataProviderModel->des) ?></td>
                                <td><?= Yii::$app->formatter->asStatus($dataProviderModel->status) ?></td>
                                <td><?= Html::button(Yii::t('app', 'Update'), ['class' => 'btn btn-block toggler' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->id]) ?></td>
                                <td><?= Html::button(Yii::t('app', 'ProductFields'), ['class' => 'btn btn-block toggler ' . ($displayState == 'saveFields' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-field-" . $dataProviderModel->id]) ?></td>
                                <td><?= Html::button(Yii::t('app', 'ProductGalleries'), ['class' => 'btn btn-block toggler' . ($isGalleryState ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-gallery-" . $dataProviderModel->id]) ?></td>
                                <td>
                                    <?=
                                    Html::a(' <span class="glyphicon glyphicon-file"></span> ' . Yii::t('app', 'Page'), Url::to([0 => 'page/index', 'entity' => Page::ENTITY_PRODUCT, 'entity_id' => $dataProviderModel->id]), [
                                        'class' => 'btn btn-default btn-block btn-social',
                                        'data-pjax' => '0',
                                    ]);
                                    ?>
                                </td>
                                <td>
                                    <?=
                                    Html::a(' <span class="glyphicon glyphicon-usd"></span> ' . Yii::t('app', 'Packages'), Url::to([0 => 'package/index', 'parent_id' => $dataProviderModel->id]), [
                                        'class' => 'btn btn-default btn-block btn-social',
                                        'data-pjax' => 0,
                                    ])
                                    ?>
                                </td>
                                <td>
                                    <?= Html::button('<span class="glyphicon glyphicon-send"></span>' . Yii::t('app', 'Telegram'), [
                                        'class' => 'btn btn-info btn-block btn-social sendTelegram',
                                        'data-url' => Url::to(['telegram/send-product-to-channel', 'product_id' => $dataProviderModel->id]),
                                    ]) ?>
                                </td>
                            </tr>
                            <tr class="" style="<?= $displayState == 'update' ? '' : 'display: none;' ?>" id="<?= "row-update-" . $dataProviderModel->id ?>">
                                <td colspan="<?= $colspan ?>">
                                    <?=
                                    $this->render('_form', [
                                        'model' => $dataProviderModel,
                                    ])
                                    ?>
                                </td>
                            </tr>
                            <tr class="" style="<?= $displayState == 'saveFields' ? '' : 'display: none;' ?>" id="<?= "row-field-" . $dataProviderModel->id ?>">
                                <td colspan="<?= $colspan ?>">
                                    <?=
                                    $this->render('_field', [
                                        'model' => $dataProviderModel,
                                        'textAreaModel' => $dataProviderTextAreaModel,
                                    ])
                                    ?>
                                </td>
                            </tr>
                            <tr class="" style="<?= $isGalleryState ? '' : 'display: none;' ?>" id="<?= "row-gallery-" . $dataProviderModel->id ?>">
                                <td colspan="<?= $colspan ?>" style="position: relative;">
                                    <?=
                                    $this->render('_gallery', [
                                        'newModel' => $newModel,
                                        'dataProviderModel' => $dataProviderModel,
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
                                'action' => Url::current(['product/index', 'state' => 'batchSave']),
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
                                    $form->field($textAreaProducts, 'values')->textarea([
                                        'rows' => count($textAreaProducts->explodeLines())
                                    ])->hint('عنوان هر محصول را در یک خط بنویسید.')
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

<?php
echo LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
    'options' => [
        'class' => 'pagination m0',
    ]
]);
?>

<?php Pjax::end(); ?>