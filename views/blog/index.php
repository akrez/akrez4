<?php

use app\assets\ChartJsAsset;
use app\components\Helper;
use app\models\Blog;
use app\models\Gallery;
use app\models\LogApi;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

ChartJsAsset::register($this);

$this->title = Blog::print('title');

$this->registerCss("
.table th, .table td {
    text-align: center;
    vertical-align: middle !important;
}
");

?>

<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-2">
                <?= Html::img(Blog::getLogoUrl(), ['class' => 'img img-responsive', 'style' => 'margin: auto;margin-top: 10px;']); ?>
            </div>
            <div class="col-sm-10">
                <h4 class="mt0" style=""><?= Blog::print('title') ?> <small><?= Blog::print('slug') ?></small></h4>
                <p class="text-justify"><?= Blog::print('des') ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <canvas id="canvas" height="85"></canvas>
    </div>
</div>
<br>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive text-center">
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $logApiFilterModel,
                'tableOptions' => ['class' => 'table table-bordered table-striped table-hover table-condensed'],
                'columns' => [
                    [
                        'attribute' => 'created_date',
                        // 'contentOptions' => ['dir' => 'ltr'],
                        'value' => function ($model) {
                            return str_replace(" ", "<br/>", $model->created_date);
                        },
                        "format" => "raw",
                    ],
                    [
                        'attribute' => 'user_agent',
                        'contentOptions' => ['dir' => 'ltr'],
                        'value' => function ($model) {
                            return Html::encode($model->user_agent);
                        },
                        'filter' => '<div class="row">
                        <div class="col-sm-6">
                            <div class="input-group">
                            ' . Html::activeLabel($logApiFilterModel, 'user_agent_like', ['class' => 'input-group-addon']) . Html::activeTextInput($logApiFilterModel, 'user_agent_like', ['class' => 'form-control']) . '
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-group">
                            ' . Html::activeLabel($logApiFilterModel, 'user_agent_not_like', ['class' => 'input-group-addon']) . Html::activeTextInput($logApiFilterModel, 'user_agent_not_like', ['class' => 'form-control']) . '
                            </div>
                        </div>
                        </div>',
                    ],
                    [
                        'label' => Yii::t('app', 'os'),
                        'value' => function ($model) {
                            $parseUserAgent = Helper::parseUserAgent($model->user_agent);
                            if ($url = Gallery::getImageUrl(Gallery::TYPE_OS, $parseUserAgent['os']['name'])) {
                                return Html::img($url, ['width' => 30]);
                            }
                            return strval($parseUserAgent['os']['name']);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'label' => Yii::t('app', 'browser'),
                        'value' => function ($model) {
                            $parseUserAgent = Helper::parseUserAgent($model->user_agent);
                            if ($url = Gallery::getImageUrl(Gallery::TYPE_BROWSER, $parseUserAgent['browser']['name'])) {
                                return Html::img($url, ['width' => 30]);
                            }
                            return strval($parseUserAgent['browser']['name']);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' =>  'ip',
                        'filter' => '<div class="row">
                        <div class="col-sm-12">
                            <div class="input-group">
                            ' . Html::activeLabel($logApiFilterModel, 'ip_not_like', ['class' => 'input-group-addon']) . Html::activeTextInput($logApiFilterModel, 'ip_not_like', ['class' => 'form-control']) . '
                            </div>
                        </div>
                        </div>',
                    ],
                    [
                        'attribute' => 'action',
                        'filter' => LogApi::$actionsList,
                        'value' => function ($model) {
                            return ucfirst($model->action);
                        },
                    ],
                    [
                        'attribute' => 'model_category_id',
                        'filter' => $list['categories'],
                        'value' => function ($model) use ($list) {
                            if (isset($list['categories'][$model->model_category_id])) {
                                return $list['categories'][$model->model_category_id];
                            }
                            return '';
                        }
                    ]
                ],
                'summary' => false,
            ])
            ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        let groupedDatas = <?= json_encode($groupedDatas) ?>;
        let dates = <?= json_encode($dates) ?>;

        let groupedDatasByDatas = {};
        groupedDatas.forEach(element => {
            if (!groupedDatasByDatas.hasOwnProperty(element.Ymd)) {
                groupedDatasByDatas[element.Ymd] = 0;
            }
            groupedDatasByDatas[element.Ymd] += parseInt(element.count);
        });

        let datas = [];
        dates.forEach(element => {
            let count = 0;
            if (groupedDatasByDatas.hasOwnProperty(element)) {
                count = groupedDatasByDatas[element];
            }
            datas.push(count);
        });

        var config = {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'بازدید',
                    fill: true,
                    borderColor: '#993799',
                    backgroundColor: 'rgba(209,165,209,0.25)',
                    spanGaps: true,
                    data: datas
                }]
            },
            options: {
                responsive: true,
                legend: false,
                title: {
                    display: true,
                    text: 'تعداد بازدید'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    rtl: true
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            maxRotation: 90,
                            minRotation: 90
                        }
                    }]
                }
            },
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'Sahel'",
        };
        var ctx = document.getElementById('canvas').getContext('2d');
        new Chart(ctx, config);
    });
</script>