<?php

use app\models\Invoice;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Invoices');
$this->registerCss("
.table td {
    vertical-align: middle !important;
    text-align: center;
}
");
?>

<h1><?= Html::encode($this->title) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        [
            'attribute' => 'status',
            'format' => 'invoiceStatus',
            'filter' => Invoice::validStatuses(),
        ],
        'customer.mobile',
        'updated_at:datetimefa',
        [
            'attribute' => 'delivery.address',
        ],
        'price:price',
        'carts_count',
        [
            'label' => '',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $grid) {
                return '<a class="btn btn-default btn-block btn-social" href="' . Url::to(['invoice/view', 'id' => $model->id]) . '" >' .
                    '<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>' .
                    Yii::t('app', 'View') .
                    '</a>';
            },
        ],
    ],
]); ?>