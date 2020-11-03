<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<div class="row">
    <div class="col-sm-4 col-xs-12">
        <img src="<?= Yii::getAlias('@web/image/reset.svg') ?>">
    </div>
    <div class="col-sm-4 col-xs-12">
        <h3 style="margin-bottom: 20px;"><?= Yii::t('app', 'ResetPasswordRequest') ?></h3>
        <?php
        $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'fieldConfig' => [
                        'template' => '<div class="input-group">{label}{input}</div>{error}',
                        'labelOptions' => [
                            'class' => 'input-group-addon',
                        ],
                    ]
        ]);
        ?>
        <?= $form->field($model, 'email')->textInput() ?>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" name="login-button" style="float: right;"> <?= Yii::t('app', 'ResetPasswordRequest') ?> </button>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
