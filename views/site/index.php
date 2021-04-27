<?php
/* @var $this yii\web\View */

use app\models\Gallery;

$this->registerCss('
p {text-align: justify;}
');
?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
                <img src="<?= Gallery::getImageUrl(null, 'sample.jpg') ?>" style="border-width: 1px;border-color: rgb(231,231,231);border-style: solid;" class="img img-responsive img-rounded">
            </div>
        </div>
    </div>
</div>