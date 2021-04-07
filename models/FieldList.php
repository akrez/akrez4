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
            'LIKE' => Yii::t('app', 'widget_like'),
            'NOT LIKE' => Yii::t('app', 'widget_not_like'),
            '=' => Yii::t('app', 'widget_equal'),
            '>=' => Yii::t('app', 'widget_bigger'),
            '<=' => Yii::t('app', 'widget_smaller'),
            '<>' => Yii::t('app', 'widget_not_equal'),
            //
            'COMBO STRING' => Yii::t('app', 'widget_combo_string'),
            'COMBO NUMBER' => Yii::t('app', 'widget_combo_number'),
            //
            'SINGLE' => Yii::t('app', 'widget_single'),
            'MULTI' => Yii::t('app', 'widget_multi'),
            //
            'BETWEEN' => Yii::t('app', 'widget_between'),
            //
            '2STATE' => Yii::t('app', 'widget_2state'),
            '3STATE' => Yii::t('app', 'widget_3state'),
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
