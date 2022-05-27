<?php

use app\assets\LeafletAsset;
use app\components\Cache;
use app\models\Customer;
use app\models\Delivery;
use app\models\Gallery;
use app\models\Invoice;
use app\models\InvoiceItem;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

const IS_CUSTOMER = false;

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

function getAttributeLabelOfDelivery($attribute)
{
    $model = Delivery::instance();
    return $model->getAttributeLabel($attribute);
}

function getPageTitle($invoice = null)
{
    return Yii::t('app', 'Invoices') . ': ' . $invoice['id'];
}

function getInvoiceValidStatuses()
{
    return Invoice::validStatuses();
}

function getUrl()
{
    return Invoice::validStatuses();
}

$deliveries = ArrayHelper::index($deliveries, 'id');
$delivery = $deliveries[$invoice['delivery_id']];

$this->title = getPageTitle($invoice);

LeafletAsset::register($this);

$this->registerCss("
");
?>

<style>
    .table td {
        vertical-align: middle !important;
        text-align: center;
    }

    .table-vertical-align-middle td,
    .table-vertical-align-middle thead th {
        vertical-align: middle;
        text-align: center;
    }

    .deprecated-panel {
        opacity: .58;
    }

    .deprecated-panel:hover {
        opacity: 1;
    }

    .max-height-256 {
        max-height: 256px;
    }

    .glyphicon-send::before {
        line-height: 1.1;
    }
</style>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<div class="row pb20">
    <div class="col-sm-12">
        <div class="btn-group btn-group-justified btn-group-md" role="group" style="margin: 0;">
            <?php
            foreach (getInvoiceValidStatuses() as $validStatusKey => $validStatus) {
                $validStatusBtnClass = 'btn-default';
                if ($validStatusKey < $invoice['status']) {
                    $validStatusBtnClass = 'btn-success';
                } elseif ($invoice['status'] == $validStatusKey) {
                    $validStatusBtnClass = 'btn-info';
                }
                echo Html::a($validStatus, Url::to([0 => 'invoice/view', 'id' => $invoice['id'], 'state' => 'setStatus', 'new_status' => $validStatusKey]), [
                    'class' => 'btn ' . $validStatusBtnClass,
                    'role' => 'button',
                ]);
            } ?>
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

<hr>

<?php
$form = ActiveForm::begin([
    'options' => ['data-pjax' => true],
    'action' => Url::current(['invoice/view', 'state' => 'newMessage', 'id' => $invoice['id']]),
    'fieldConfig' => [
        'template' => '<div class="input-group">{input}<span class="input-group-btn"> ' .
            Html::submitButton(' <span class="glyphicon glyphicon-send"></span> ', ['class' => 'btn btn-success']) .
            '</span></div>',
        'labelOptions' => [
            'class' => 'input-group-addon',
        ],
    ]
]);
?>
<div class="row">
    <div class="col-sm-10">
        <?= $form->field($invoiceMessageModel, 'message')->textInput(); ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$dataParts = [];
foreach ([
    'invoiceMessages' => $invoiceMessages,
    'invoiceStatuses' => $invoiceStatuses,
    'payments' => $payments,
    'deliveries' => $deliveries,
] as $dataKey => $dataArray) {
    foreach ($dataArray as $dataValue) {
        $dataParts[$dataValue['created_at']][$dataKey][$dataValue['id']] = $dataValue;
    }
}

krsort($dataParts);

foreach ($dataParts as $dataCreatedAt => $dataPart) {
    $dataCreatedAtFa = Yii::$app->formatter->asDatetimefa($dataCreatedAt);
    foreach ($dataPart as $dataPartName => $dataValues) {
        foreach ($dataValues as $dataValue) {
?>

            <?php if ($dataPartName == 'deliveries') { ?>
                <div class="panel panel-default <?= ($dataValue['id'] == $invoice['delivery_id'] ? '' : 'deprecated-panel') ?>">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-pushpin"></span>
                        <small><?= $dataCreatedAtFa ?></small>
                    </div>
                    <?php echo $this->render('../delivery/_delivery_table', ['delivery' => $dataValue]); ?>
                </div>
            <?php } ?>

            <?php if ($dataPartName == 'payments') { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-list-alt"></span>
                        <small><?= $dataCreatedAtFa ?></small>
                    </div>
                    <div class="panel-body">
                        <a href="<?= Gallery::getImageUrl(Gallery::TYPE_PAYMENT, $dataValue['payment_name']) ?>" target="_blank">
                            <img class="img img-responsive max-height-256" src="<?= Gallery::getImageUrl(Gallery::TYPE_PAYMENT, $dataValue['payment_name']) ?>">
                        </a>
                    </div>
                </div>
            <?php } ?>

            <?php if ($dataPartName == 'invoiceStatuses') { ?>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-bell"></span>
                        <small><?= $dataCreatedAtFa ?></small>
                        <br>
                        <?= $dataValue['message'] ?>
                    </div>
                </div>
            <?php } ?>

            <?php if ($dataPartName == 'invoiceMessages') {
                $isMineMessage = (IS_CUSTOMER == $dataValue['is_customer']);
            ?>
                <div class="row">
                    <?php if (!$isMineMessage) { ?>
                        <div class="col-sm-2">
                        </div>
                    <?php } ?>
                    <div class="col-sm-10">
                        <div class="panel <?= (!$isMineMessage ? 'panel-default' : 'panel-success') ?>">
                            <div class="panel-heading">
                                <span class="glyphicon glyphicon-comment"></span>
                                <small><?= $dataCreatedAtFa ?></small>
                                <br>
                                <?= HtmlPurifier::process($dataValue['message']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

<?php
        }
    }
}
?>