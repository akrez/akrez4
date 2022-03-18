<?php

use app\assets\LeafletAsset;
use app\components\Cache;
use app\components\Helper;
use app\models\Customer;
use app\models\Gallery;
use app\models\Invoice;
use app\models\InvoiceItem;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


function getAttributeLabelOfCustomer($attribute)
{
    $model = Customer::instance();
    return $model->getAttributeLabel($attribute);
}

function getAttributeLabelOfInvoice($attribute)
{
    $model = Invoice::instance();
    return $model->getAttributeLabel($attribute);
}

function getAttributeLabelOfInvoiceItem($attribute)
{
    $model = InvoiceItem::instance();
    return $model->getAttributeLabel($attribute);
}

$deliveries = ArrayHelper::index($deliveries, 'id');
$delivery = $deliveries[$invoice['delivery_id']];

$this->title = Yii::t('app', 'Invoices') . ': ' . $invoice['id'];

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
.wizard-steps {
    direction: ltr;
}
.wizard-steps.btn-group > .btn:first-child:not(:last-child):not(.dropdown-toggle) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 4px;
    border-top-left-radius: 4px;
}
.wizard-steps.btn-group > .btn:last-child:not(:first-child), .btn-group > .dropdown-toggle:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 4px;
    border-top-right-radius: 4px;
}
");
?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<div class="row">
    <div class="col-sm-12 pb20">
        <div class="btn-group btn-group-justified btn-group-lg wizard-steps" role="group" style="margin: 0;">
            <?php
            foreach (Invoice::validStatuses() as $validStatusKey => $validStatus) {
                $validStatusBtnClass = 'btn-default';
                if ($validStatusKey < $invoice['status']) {
                    $validStatusBtnClass = 'btn-success';
                } elseif ($invoice['status'] == $validStatusKey) {
                    $validStatusBtnClass = 'btn-info';
                }
            ?>
                <a href="#" class="btn <?= $validStatusBtnClass ?>" role="button"> <?= $validStatus ?> </a>
            <?php } ?>
        </div>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => [$invoice],
        'modelClass' => InvoiceItem::class,
        'sort' => false,
        'pagination' => false,
    ]),
    'filterModel' => null,
    'layout' => "{items}\n{pager}",
    'columns' => [
        [
            'attribute' => 'id',
            'label' => getAttributeLabelOfInvoice('id'),
        ],
        [
            'attribute' => 'status',
            'label' => getAttributeLabelOfInvoice('status'),
            'format' => 'invoiceStatus',
        ],
        [
            'attribute' => 'created_at',
            'label' => getAttributeLabelOfInvoice('created_at'),
            'format' => 'datetimefa',
        ],
        [
            'attribute' => 'price',
            'label' => getAttributeLabelOfInvoice('price'),
            'format' => 'price',
        ],
        [
            'attribute' => 'carts_count',
            'label' => getAttributeLabelOfInvoice('carts_count'),
        ],
        [
            'attribute' => 'des',
            'label' => getAttributeLabelOfInvoice('des'),
        ],
        [
            'label' => getAttributeLabelOfCustomer('mobile'),
            'format' => 'raw',
            'value' => function ($model, $key, $index, $grid) use ($customer) {
                if (isset($customer['mobile'])) {
                    return $customer['mobile'];
                }
                return '';
            },
        ],
    ],
]); ?>

<?= GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $invoiceItems,
        'modelClass' => InvoiceItem::class,
        'sort' => false,
        'pagination' => false,
    ]),
    'filterModel' => null,
    'columns' => [
        [
            'attribute' => 'code',
            'label' => getAttributeLabelOfInvoiceItem('code'),
        ],
        [
            'attribute' => 'image',
            'label' => getAttributeLabelOfInvoiceItem('image'),
            'format' => 'raw',
            'value' => function ($model, $key, $index, $grid) {
                $src = Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $model['image']);
                $img = Html::img($src, [
                    "style" => "max-height: 40px;",
                ]);
                return Html::a($img, $src, ['target' => '_blank']);
            },
        ],
        [
            'attribute' => 'title',
            'label' => getAttributeLabelOfInvoiceItem('title'),
        ],
        [
            'attribute' => 'color_code',
            'label' => getAttributeLabelOfInvoiceItem('color_code'),
            'format' => 'raw',
            'value' => function ($model, $key, $index, $grid) {
                if ($model['color_code']) {
                    return '<span class="color-class" style="background-color: ' . $model['color_code'] . ';">⠀⠀</span> ' . Cache::getBlogCacheColorLabel(Yii::$app->user->getIdentity(), $model['color_code']);
                }
                return '';
            },
        ],
        [
            'attribute' => 'guaranty',
            'label' => getAttributeLabelOfInvoiceItem('guaranty'),
        ],
        [
            'attribute' => 'des',
            'label' => getAttributeLabelOfInvoiceItem('des'),
        ],
        [
            'attribute' => 'price',
            'label' => getAttributeLabelOfInvoiceItem('price'),
            'format' => 'price',
        ],
        [
            'attribute' => 'cnt',
            'label' => getAttributeLabelOfInvoiceItem('cnt'),
        ],
    ],
]); ?>

<?= $this->render('../delivery/_delivery_table', ['delivery' => $delivery]) ?>