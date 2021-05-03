<?php

namespace app\models;

use Yii;

class FieldList extends Model
{
    public static function opertaionsList()
    {
        return [
            'LIKE' => Yii::t('app', 'LIKE'),
            'NOT LIKE' => Yii::t('app', 'NOT LIKE'),
            '=' => Yii::t('app', 'EQUAL'),
            '<>' => Yii::t('app', 'NOT EQUAL'),
            'IN' => Yii::t('app', 'IN'),
            'NOT IN' => Yii::t('app', 'NOT IN'),
            '>=' => Yii::t('app', 'BIGGER THAN'),
            '<=' => Yii::t('app', 'SMALLER THAN'),
            'BETWEEN' => Yii::t('app', 'BETWEEN'),
        ];
    }

    public static function widgetsList()
    {
        return [
            'LIKE' => Yii::t('app', 'LIKE'),
            'NOT LIKE' => Yii::t('app', 'NOT LIKE'),
            '=' => Yii::t('app', 'EQUAL'),
            '>=' => Yii::t('app', 'BIGGER'),
            '<=' => Yii::t('app', 'SMALLER'),
            '<>' => Yii::t('app', 'NOT EQUAL'),
            //
            'COMBO STRING' => Yii::t('app', 'COMBO STRING'),
            'COMBO NUMBER' => Yii::t('app', 'COMBO NUMBER'),
            //
            'SINGLE' => Yii::t('app', 'SINGLE'),
            'MULTI' => Yii::t('app', 'MULTI'),
            //
            'BETWEEN' => Yii::t('app', 'BETWEEN'),
            //
            '2STATE' => Yii::t('app', '2STATE'),
            '3STATE' => Yii::t('app', '3STATE'),
        ];
    }

    public static function getPluralOperations()
    {
        return ['IN', 'NOT IN',];
    }

    public static function getMinMaxOperations()
    {
        return ['BETWEEN',];
    }
}
