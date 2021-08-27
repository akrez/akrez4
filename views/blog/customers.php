<?php

use app\models\Customer;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Customers');
?>
<div class="customer-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'created_at',
                'format' => 'datetimefa',
                'filter' => false,
            ],
            [
                'attribute' => 'status',
                'format' => 'status',
                'filter' => Customer::validStatuses(true),
            ],
            [
                'attribute' => 'verify_at',
                'format' => 'datetimefa',
                'filter' => false,
            ],
            'mobile',
            //'name',
        ],
    ]); ?>
</div>