<?php

use app\components\Alert;
use app\models\Gallery;
use app\models\Page;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$title = Yii::t('app', 'Profile');
$this->title = $title;

$this->registerCss("
.table th {
    vertical-align: top !important;
    white-space: nowrap;
}
.table td {
    vertical-align: middle !important;
}
.max-height-34 {
    max-height: 34px;
}    
");
$colspan = 4;
$this->registerJs("
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
$(document).on('pjax:beforeSend', function(xhr, options) {
    $('.ajax-splash-show').css('display','inline-block');
    $('.ajax-splash-hide').css('display','none');
});
$(document).on('pjax:complete', function(xhr, textStatus, options) {
    $('.ajax-splash-show').css('display','none');
    $('.ajax-splash-hide').css('display','inline-block');
    applyFilter();
});
", View::POS_END);
?>

<h3 class="pb20"><?= $this->title ?></h3>

<?php
Pjax::begin([
    'id' => "blog-pjax",
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
            <div class="panel-heading">
                <?= Html::encode($model->name) ?>
            </div>
            <table id="table" class="table table-bordered table-condensed">
                <tbody>
                    <?php foreach ([$model] as $dataProviderModelKey => $dataProviderModel) : ?>
                        <?php
                        $trCssClass = ($dataProviderModelKey % 2 == 0 ? 'active' : '');
                        //
                        $displayState = null;
                        $isGalleryState = false;
                        //
                        if ($model && $model->id == $dataProviderModel->name) {
                            $displayState = $state;
                            $dataProviderModel = $model;
                            //
                            if (in_array($state, ['galleryDefault', 'galleryDelete', 'galleryUpload'])) {
                                $isGalleryState = true;
                            }
                        }
                        ?>
                        <tr class="active">
                            <td class="text-center">
                                <?php if ($dataProviderModel->logo) : ?>
                                    <img class="img max-height-34" src="<?= Gallery::getImageUrl(Gallery::TYPE_LOGO, $dataProviderModel->logo) ?>">
                                <?php endif; ?>
                            </td>
                            <td><?= Html::button(Yii::t('app', 'Update {name}', ['name' => Yii::t('app', 'Profile')]), ['class' => 'btn btn-block toggler' . ($displayState == 'update' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-update-" . $dataProviderModel->name]) ?></td>
                            <td><?= Html::button(Yii::t('app', 'Choose Logo'), ['class' => 'btn btn-block toggler' . ($isGalleryState ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-gallery-" . $dataProviderModel->name]) ?></td>
                            <td><?= Html::button(Yii::t('app', 'Change Password'), ['class' => 'btn btn-block toggler' . ($displayState == 'password' ? ' btn-warning ' : ' btn-default '), 'toggle' => "#row-password-" . $dataProviderModel->name]) ?></td>
                        </tr>
                        <tr class="" style="<?= $displayState == 'update' ? '' : 'display: none;' ?>" id="<?= "row-update-" . $dataProviderModel->name ?>">
                            <td colspan="<?= $colspan ?>">
                                <?php
                                echo $this->render('_form', [
                                    'model' => $dataProviderModel,
                                ]);
                                ?>
                            </td>
                        </tr>
                        <tr class="" style="<?= $isGalleryState ? '' : 'display: none;' ?>" id="<?= "row-gallery-" . $dataProviderModel->name ?>">
                            <td colspan="<?= $colspan ?>" style="position: relative;">
                                <?php
                                echo $this->render('_gallery', [
                                    'dataProviderModel' => $dataProviderModel,
                                ]);
                                ?>
                            </td>
                        </tr>
                        <tr class="" style="<?= $displayState == 'password' ? '' : 'display: none;' ?>" id="<?= "row-password-" . $dataProviderModel->name ?>">
                            <td colspan="<?= $colspan ?>">
                                <?php
                                echo $this->render('_password', [
                                    'model' => $dataProviderModel,
                                ]);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Pjax::end(); ?>