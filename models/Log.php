<?php

namespace app\models;

use yii\db\ActiveRecord as BaseActiveRecord;

class Log extends BaseActiveRecord
{
    protected static $_data = [];

    public static function getDb()
    {
        return \Yii::$app->db;
    }

    public static function setData($params = [])
    {
        self::$_data = self::$_data + $params;
    }

    public function attributeLabels()
    {
        return [
            'ip' => 'IP',
            'user_agent' => 'User Agent',
            'action' => 'Action',
            'created_date' => 'تاریخ',
            'model_category_id' => 'دسته‌بندی',
            'model_customer_id' => 'مشتری',
            'user_agent_like' => 'شامل باشد',
            'user_agent_not_like' => 'شامل نباشد',
            'ip_not_like' => 'شامل نباشد',
        ];
    }
}
