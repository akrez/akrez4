<?php

namespace app\models;

use yii\db\ActiveRecord as BaseActiveRecord;

class Log extends BaseActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blog_name' => 'Blog Name',
            'ip' => 'Ip',
            'method' => 'Method',
            'is_ajax' => 'Is Ajax',
            'url' => 'Url',
            'response_http_code' => 'Response Http Code',
            'created_date' => 'Created Date',
            'created_time' => 'Created Time',
            'data_post' => 'Data Post',
            'user_agent' => 'User Agent',
            'controller' => 'Controller',
            'action' => 'Action',
            'model_id' => 'Model ID',
            'model_parent_id' => 'Model Parent ID',
        ];
    }
}
