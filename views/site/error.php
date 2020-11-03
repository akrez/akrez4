<?php

use yii\helpers\Html;

$this->title = $name;

?>


<div class="jumbotron">
    <h1><?= Html::encode($exception->statusCode) ?></h1>
    <p><?= nl2br(Html::encode($message)) ?></p>
</div>
