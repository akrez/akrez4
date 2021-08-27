<?php

use app\assets\LeafletAsset;
use app\components\Cache;
use app\models\City;
use app\models\Gallery;
use app\models\Status;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Invoices') . ': ' . $invoice->id;

LeafletAsset::register($this);

$this->registerCss("
.table td {
    vertical-align: middle !important;
    text-align: center;
}
.table-vertical-align-middle td,
.table-vertical-align-middle thead th {
    vertical-align: middle;
    text-align: center;
}
");

$this->registerJs('
var latLng = ' . json_encode([$invoice->lat,  $invoice->lng,]) . ';
var map = L.map("map", {
    center: latLng,
    zoom: 14
});
//map.dragging.disable();
var osmUrl = "http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
var osmLayer = new L.TileLayer(osmUrl, {
    maxZoom: 19
});
map.addLayer(osmLayer);

var marker = L.marker(latLng).addTo(map);
map.on("move", function() {
    marker.setLatLng(latLng);
});
map.on("dragend", function() {
    marker.setLatLng(latLng);
});
');
?>

<h1><?= Html::encode($this->title) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => null,
    'columns' => [
        'product.code',
        [
            'attribute' => 'image',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $grid) {
                $src = Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $model->product->image);
                $img = Html::img($src, [
                    "style" => "max-height: 40px;",
                ]);
                return Html::a($img, $src, ['target' => '_blank']);
            },
        ],
        'product.title',
        [
            'attribute' => 'package.color',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $grid) {
                if ($model->package) {
                    return '<span class="color-class" style="background-color: ' . $model->package->color_code . ';">⠀⠀</span> ' . Cache::getBlogCacheColorLabel(Yii::$app->user->getIdentity(), $model->package->color_code);
                }
                return '';
            },
        ],
        'package.guaranty',
        'package.des',
        'package.price:price',
        'cnt',
    ],
]); ?>

<div class="row">
    <div class="col-sm-12 pb20">
        <table class="table table-bordered table-sm table-hover">
            <tbody>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('name') ?></td>
                    <td colspan="3"><?= HtmlPurifier::process($invoice->name) ?></td>
                    <td class="active"><?= $invoice->getAttributeLabel('updated_at') ?></td>
                    <td><?= Yii::$app->formatter->asDatetimefa($invoice->updated_at) ?></td>
                    <td class="active"><?= $invoice->getAttributeLabel('created_at') ?></td>
                    <td><?= Yii::$app->formatter->asDatetimefa($invoice->created_at) ?></td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('phone') ?></td>
                    <td><?= HtmlPurifier::process($invoice->phone) ?></td>
                    <td class="active"><?= $invoice->getAttributeLabel('mobile') ?></td>
                    <td><?= HtmlPurifier::process($invoice->mobile) ?></td>
                    <td colspan="4" rowspan="7" style="height: inherit;position: relative;">
                        <div id="map" style="position: absolute;top: 0;bottom: 0;right: 0;left: 0;"></div>
                    </td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('city') ?></td>
                    <td><?= City::getLabel($invoice->city) ?></td>
                    <td class="active"><?= $invoice->getAttributeLabel('postal_code') ?></td>
                    <td><?= HtmlPurifier::process($invoice->postal_code) ?></td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('address') ?></td>
                    <td colspan="3"><?= HtmlPurifier::process($invoice->address) ?></td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('des') ?></td>
                    <td colspan="3"><?= HtmlPurifier::process($invoice->des) ?></td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('price') ?></td>
                    <td colspan="3"><?= Yii::$app->formatter->asPrice($invoice->price) ?></td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('receipt') ?></td>
                    <td colspan="3" class="text-center">
                        <?php
                        $src = Gallery::getImageUrl(Gallery::TYPE_RECEIPT, $invoice->receipt);
                        $img = Html::img($src, [
                            "style" => "max-height: 40px;",
                        ]);
                        echo Html::a($img, $src, ['target' => '_blank']);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="active"><?= $invoice->getAttributeLabel('pay_status') ?></td>
                    <td colspan="3" class="text-center">
                        <?php
                        if ($invoice->pay_status == Status::STATUS_UNVERIFIED) {
                            echo Html::a(Status::getLabel(Status::STATUS_ACTIVE), [
                                'invoice/set-status',
                                'id' => $invoice->id,
                                'attribute' => 'pay_status',
                                'status' => Status::STATUS_ACTIVE,
                            ], ['class' => 'btn btn-success']);
                            //
                            echo Html::a(Status::getLabel(Status::STATUS_DISABLE), [
                                'invoice/set-status',
                                'id' => $invoice->id,
                                'attribute' => 'pay_status',
                                'status' => Status::STATUS_DISABLE,
                            ], ['class' => 'btn btn-danger']);
                        } else {
                            echo Status::getLabel($invoice->pay_status);
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>