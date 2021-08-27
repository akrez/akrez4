<?php

use app\models\City;
use app\models\Gallery;
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
        'price:price',
        'carts_count',
        'updated_at:datetimefa',
        'name',
        'phone',
        'mobile',
        [
            'attribute' => 'city',
            'value' => function ($model, $key, $index, $grid) {
                return City::getLabel($model->city);
            },
            'filter' => true,
        ],
        [
            'attribute' => 'receipt',
            'value' => function ($model, $key, $index, $grid) {
                $src = Gallery::getImageUrl(Gallery::TYPE_RECEIPT, $model->receipt);
                $img = Html::img($src, [
                    "style" => "max-height: 40px;",
                ]);
                return Html::a($img, $src, ['target' => '_blank']);
            },
            'filter' => false,
            'format' => 'raw',
            'enableSorting' => false
        ],
        [
            'attribute' => 'pay_status',
            'format' => 'status',
            'filter' => Invoice::validStatuses(),
        ],
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