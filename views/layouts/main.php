<?php

use app\assets\SiteAsset;
use app\components\Alert;
use app\models\Blog;
use yii\helpers\Html;
use yii\helpers\Url;

SiteAsset::register($this);
$this->title = ($this->title ? $this->title : Yii::$app->name);
$this->registerCss("
.splash-style {
    background-image: url('" . Yii::getAlias('@web/image/loading.svg') . "');
}
");
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= Yii::getAlias('@web/favicon.svg') ?>" type="image/svg+xml">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>

    <div class="container mt20">
        <nav id="navbar" class="navbar navbar-default">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?= Yii::$app->homeUrl ?>"><?= Yii::$app->name ?></a>
            </div>
            <div id="navbar-collapse" class="collapse navbar-collapse">
                <?php if (!Yii::$app->user->isGuest) : ?>
                    <ul class="nav navbar-nav">
                        <li><a href="<?= Url::toRoute(['/site/blog']) ?>"><?= Blog::print('title') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/category']) ?>"><?= Yii::t('app', 'Categories') ?></a></li>
                    </ul>
                <?php endif; ?>

                <ul class="nav navbar-nav navbar-left">
                    <?php if (Yii::$app->user->isGuest) : ?>
                        <li><a href="<?= Url::toRoute(['/site/signup']) ?>"><?= Yii::t('app', 'Signup') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/site/signin']) ?>" style="padding-left: 0;"><?= Yii::t('app', 'Signin') ?></a></li>
                    <?php else : ?>
                        <li><a href="<?= Yii::$app->params['urlToBlog'](Blog::print('name')) ?>" target="_blank"><?= Yii::t('app', 'GoToBlog') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/site/profile']) ?>"><?= Yii::t('app', 'Profile') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/site/signout']) ?>"><?= Yii::t('app', 'Signout') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>