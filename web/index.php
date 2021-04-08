<?php

function v(...$input)
{
    foreach ($input as $i) {
        var_dump($i);
    }
    die;
}

function jd(...$input)
{
    die(json_encode($input));
}

function ed($input)
{
    var_export($input);
    die;
}

$params = require(__DIR__ . '/../config/params.php');

require VENDOR_PATH . '/autoload.php';
require VENDOR_PATH . '/yiisoft/yii2/Yii.php';

$config = [
    'id' => 'basic',
    'name' => APP_NAME,
    'language' => 'fa-IR',
    'bootstrap' => [
        'log',
    ],
    'basePath' => BASE_PATH,
    'vendorPath' => VENDOR_PATH,
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerNamespace' => 'app\controllers',
    'components' => [
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js' => [
                        YII_ENV !== 'prod' ? 'jquery.js' : 'jquery.min.js'
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [
                        YII_ENV !== 'prod' ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
                    ]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => [
                        YII_ENV !== 'prod' ? 'js/bootstrap.js' : 'js/bootstrap.min.js',
                    ]
                ]
            ],
        ],
        'db' => $params['db'],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-akrez4',
            'cookieValidationKey' => $params['cookieValidationKey'],
            'baseUrl' => $params['baseUrl'],
        ],
        'session' => [
            'name' => 'akrez-akrez4',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Blog',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-akrez4', 'httpOnly' => true],
        ],
        'customerApi' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Customer',
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
            'returnUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'site/gallery/<type:\w+>/<whq>/<name:[\w\.]+>' => 'site/gallery',
                //
                '<module:(api)>/<controller>/<action>' => '<module>/<controller>/<action>',
                //
                '<controller>/<action>/<id:\d+>' => '<controller>/<action>',
                '<controller>/<action>' => '<controller>/<action>',
                '<controller>/' => '<controller>/index',
                //
                '' => 'site/index',
            ],
        ],
        'formatter' => [
            'class' => 'app\components\Formatter',
        ],
        'mailer' => $params['mailer'],
    ],
    'params' => $params['params'],
];

if (YII_ENV == 'dev') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

(new yii\web\Application($config))->run();
