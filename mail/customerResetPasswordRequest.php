<div style="direction: rtl">
    <h4> <?= $_title ?> </h4>
    <?= $customer->getAttributeLabel('email') ?>: <?= $customer->email ?>
    <br>
    <?= $customer->getAttributeLabel('reset_token') ?>: <?= $customer->reset_token ?>
</div>