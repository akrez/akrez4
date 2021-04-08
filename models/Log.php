<?php

namespace app\models;

use yii\db\ActiveRecord as BaseActiveRecord;

class Log extends BaseActiveRecord
{
    public function attributeLabels()
    {
        return Model::attributeLabelsList();
    }
}
