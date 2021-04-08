<?php

use yii\helpers\Html;

$encodedMessage = Html::encode($message);
$this->title = $encodedMessage;

?>


<div class="jumbotron">
    <h1><?= Html::encode($exception->statusCode) ?></h1>
    <p><?= nl2br($encodedMessage) ?></p>
</div>
