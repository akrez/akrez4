<?php

use app\assets\SiteAsset;
use app\components\Alert;
use app\models\Blog;
use app\models\Gallery;
use app\models\Page;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

SiteAsset::register($this);
$this->title = ($this->title ? $this->title : Yii::$app->name);
$this->registerCss("
.splash-style {
    background-image: url('" . Gallery::getImageUrl(null, 'loading.svg') . "');
}
");
$format = <<< SCRIPT
function inputDecimalSeparator(Number) {
    var commaCounter = 10;
    Number += '';

    for (var i = 0; i < commaCounter; i++) {
        Number = Number.replace(',', '');
    }

    x = Number.split('.');
    y = x[0];
    z = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;

    while (rgx.test(y)) {
        y = y.replace(rgx, '$1' + ',' + '$2');
    }
    commaCounter++;
    return y + z;
}
$(document).on('keypress , paste', '.input-decimal-separator', function(e) {
    $('.input-decimal-separator').on('input', function() {
        e.target.value = inputDecimalSeparator(e.target.value);
    });
});
SCRIPT;
$this->registerJs($format, View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= Gallery::getImageUrl(null, 'favicon.svg') ?>" type="image/svg+xml">
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
                        <li><a href="<?= Url::toRoute(['/blog/index']) ?>"><?= Blog::print('title') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/invoice/index']) ?>"><?= Yii::t('app', 'Invoices') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/category']) ?>"><?= Yii::t('app', 'Categories') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/blog/customers']) ?>"><?= Yii::t('app', 'Customers') ?></a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= Yii::t('app', 'Settings') ?><span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= Url::toRoute(['/blog/profile']) ?>"><?= Yii::t('app', 'Profile') ?></a></li>
                                <li><a href="<?= Url::toRoute(['/color/index']) ?>"><?= Yii::t('app', 'Colors') ?></a></li>
                                <li><a href="<?= Url::toRoute(['/financial-account/index']) ?>"><?= Yii::t('app', 'Financial Account') ?></a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= Yii::t('app', 'Pages') ?><span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <?php foreach (Page::entityPageList(Page::ENTITY_BLOG) as $entityBlog => $entityBlogName) { ?>
                                    <li><a href="<?= Url::toRoute(['/page', 'entity' => Page::ENTITY_BLOG, 'page_type' => $entityBlog, 'page_type' => $entityBlog, 'entity_id' => Yii::$app->user->getId(),]) ?>"><?= Yii::t('app', 'Page') . ' ' . $entityBlogName ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>

                <ul class="nav navbar-nav navbar-left">
                    <?php if (Yii::$app->user->isGuest) : ?>
                        <li><a href="<?= Url::toRoute(['/site/signup']) ?>"><?= Yii::t('app', 'Signup') ?></a></li>
                        <li><a href="<?= Url::toRoute(['/site/signin']) ?>" style="padding-left: 0;"><?= Yii::t('app', 'Signin') ?></a></li>
                    <?php else : ?>
                        <li><a href="<?= Yii::$app->params['urlToBlog'](Blog::print('name')) ?>" target="_blank"><?= Yii::t('app', 'GoToBlog') ?></a></li>
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