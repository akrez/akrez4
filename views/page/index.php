<?php

use app\models\Page;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\assets\MediumEditorAsset;

MediumEditorAsset::register($this);

$this->title = Yii::t('app', 'Page')  . ' ' . Yii::t('app', $model->entity) . ': ' .  ($entityModel ? $entityModel->title : Yii::t('app', $model->entity_id));

$this->registerCss("
#editable {
    display: none;
}
");

$this->registerJs("var editor = new MediumEditor('#editable')");
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-sm-12">
        <?= $form->field($model, 'body')->textarea([
            'id' => "editable", 'style' => "height: auto; min-height: 240px;",
        ])->label(false) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'status')->dropDownList(Page::validStatuses())->label(false) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-3">
        <?= Html::submitButton($model->isNewRecord ? ' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create') : ' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), ['class' => 'btn btn-block btn-social ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]); ?>
    </div>
    <div class="col-sm-6">
    </div>
    <div class="col-sm-3">
        <?php
        $url = null;
        if ($entityModel && $model->entity == Page::ENTITY_CATEGORY) {
            $url = Url::to([0 => 'category/index']);
        } else if ($entityModel && $model->entity == Page::ENTITY_PRODUCT) {
            $url = Url::to([0 => 'product/index', 'parent_id' => $entityModel->category_id]);
        }
        if ($url) {
            echo Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), $url, ['class' => 'btn btn-default btn-block btn-social']);
        }
        ?>
    </div>
</div>

<?php ActiveForm::end(); ?>