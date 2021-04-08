<?php

namespace app\models;

use app\components\Helper;

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
class LogAdmin extends Log
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
            [['is_ajax', 'response_http_code'], 'integer'],
            [['data_post'], 'string', 'max' => 4096],
            [['blog_name', 'ip', 'controller', 'action', 'model_id', 'model_parent_id'], 'string', 'max' => 60],
            [['method', 'created_date', 'created_time'], 'string', 'max' => 11],
            [['url', 'user_agent'], 'string', 'max' => 2047],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function log($params = [])
    {
        $template = [
            'blog_name' => null,
            'ip' => null,
            'method' => null,
            'is_ajax' => null,
            'url' => null,
            'response_http_code' => null,
            'created_date' => null,
            'created_time' => null,
            'data_post' => null,
            'user_agent' => null,
            'controller' => null,
            'action' => null,
            'model_id' => null,
            'model_parent_id' => null,
        ];
        $data = Helper::templatedArray($template, $params);
        return static::getDb()->createCommand()->insert(self::tableName(), $data)->execute();
    }
}
