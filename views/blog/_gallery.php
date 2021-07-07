<?php

use app\components\Image;
use app\models\Gallery;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

$this->registerCss("
    .akrez-container {
        position: relative;
        width: 100%;
        padding-top: 100%;  
    }
    .akrez-text {
        position:  absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        font-size: 20px;
        
        background-repeat: no-repeat;
        background-size: contain;
        background-position-x: center;
        background-position-y: center;
    }
");
$id = $dataProviderModel->name;
$actionUrl = 'blog/profile';
$buttonSelector = "gallery-button-" . $id;
$modeSelector = "gallery-mode-" . $id;
$formSelector = "gallery-form-" . $id;
?>

<div class="container-fluid">

    <?php
    $form = ActiveForm::begin([
        'action' => Url::current([$actionUrl, 'state' => 'galleryUpload']),
        'method' => 'post',
        'options' => [
            'data-pjax' => true,
            'enctype' => 'multipart/form-data',
        ],
    ]);
    ?>

    <div class="row mt20">
        <?php foreach (Image::getValidModes() as $validModeKey => $validModeValue) { ?>
            <div class="col-sm-4">
                <?= Html::a(' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'UploadNewImage') . ' (' . $validModeValue . ')', 'javascript:void(0);', ['class' => 'btn btn-success btn-block btn-social', "onclick" => "$('#" . $modeSelector . "').val(" . $validModeKey . "); $('#" . $buttonSelector . "').click();"]); ?>
            </div>
        <?php } ?>
    </div>

    <div class="row mt10">
        <div class="col-sm-12">
            <?php
            echo $form
                ->field($dataProviderModel, 'image')
                ->fileInput([
                    'id' => $buttonSelector,
                    'class' => "gallery-file-input",
                    'onchange' => 'galleryFormSubmit(this);',
                    'style' => 'display: none',
                ])->label(false);
            echo Html::hiddenInput('mode', Image::MODE_NONE, ['id' => $modeSelector]);
            ?>
        </div>
    </div>

    <div class='row'>
        <?php foreach ($dataProviderModel->galleries as $gallery) : ?>
            <div class="col-sm-3 pb15">
                <div class="thumbnail akrez-container" style="<?= $dataProviderModel->logo == $gallery->name ? 'border-color: #e89929; box-shadow: 0 1px 2px #f2b968;' : '' ?>">
                    <div class="akrez-text" style="background-image: url('<?= Gallery::getImageUrl(Gallery::TYPE_LOGO, $gallery->name) ?>');">
                        <div style="top: 5px; position: absolute; right: 5px;">
                            <a class="btn btn-sm btn-warning btn-social" <?= $dataProviderModel->logo == $gallery->name ? 'disabled' : '' ?> href="<?= Url::current([0 => $actionUrl, 'state' => 'galleryDefault', 'name' => $gallery->name]) ?>">
                                <span class="glyphicon glyphicon-star"></span><?= Yii::t('app', 'Default') ?>
                            </a>
                        </div>
                        <div style="bottom: 5px; position: absolute; right: 5px;">
                            <a class="btn btn-sm btn-danger btn-social" href="<?= Url::current([0 => $actionUrl, 'state' => 'galleryDelete', 'name' => $gallery->name]) ?>" data-confirm="<?= Yii::t('yii', 'Are you sure you want to delete this item?') ?>" role="button">
                                <span class="glyphicon glyphicon-trash"></span> <?= Yii::t('yii', 'Delete') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>