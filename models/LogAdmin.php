<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_admin".
 *
 * @property int $id
 * @property string|null $blog_name
 * @property string|null $ip
 * @property string|null $method
 * @property int|null $is_ajax
 * @property string|null $url
 * @property int|null $duration
 * @property int|null $memory
 * @property int|null $response_http_code
 * @property string|null $created_date
 * @property string|null $created_time
 * @property string|null $data_post
 * @property string|null $user_agent
 * @property string|null $controller
 * @property string|null $action
 * @property string|null $model_id
 * @property string|null $model_parent_id
 */
class LogAdmin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_ajax', 'duration', 'memory', 'response_http_code'], 'integer'],
            [['data_post'], 'string'],
            [['blog_name', 'ip', 'controller', 'action', 'model_id', 'model_parent_id'], 'string', 'max' => 60],
            [['method', 'created_date', 'created_time'], 'string', 'max' => 11],
            [['url', 'user_agent'], 'string', 'max' => 2047],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blog_name' => 'Blog Name',
            'ip' => 'Ip',
            'method' => 'Method',
            'is_ajax' => 'Is Ajax',
            'url' => 'Url',
            'duration' => 'Duration',
            'memory' => 'Memory',
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
