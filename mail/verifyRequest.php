<?php

use yii\helpers\Url;
?>

<div style="direction: rtl">
    <h4> <?= $_title ?> </h4>
    <?= $user->getAttributeLabel('email') ?>: <?= $user->email ?>
    <br>
    <?= $user->getAttributeLabel('verify_token') ?>: <?= $user->verify_token ?>
    <br>
    <a href="<?= Url::to(['site/verify-password', 'email' => $user->email, 'verify_token' => $user->verify_token], true) ?>"><?= Yii::t('app', 'VerifyPassword') ?></a>
</div>