<?php

namespace app\models;

use app\components\Helper;
use app\components\Jdf;
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
        $template = $params + [
            'ip' => Yii::$app->request->getUserIP(),
            'created_date' => Jdf::jdate('Y-m-d'),
            'created_time' => date('H:i:s'),
            'blog_name' => Yii::$app->user->getId(),
            'user_agent' => Yii::$app->request->getUserAgent(),
            'data_post' => json_encode(Yii::$app->request->post()),
            'method' => Yii::$app->request->method,
            'is_ajax' => Yii::$app->request->isAjax,
            'url' => $_SERVER['REQUEST_URI'],
            'response_http_code' => Yii::$app->response->statusCode,
            'controller' => Yii::$app->controller->id,
            'action' => Yii::$app->controller->action->id,
            'model_id' => Yii::$app->request->get('id'),
            'model_parent_id' => Yii::$app->request->get('parent_id'),
        ];
        $data = Helper::templatedArray($template, $params);
        return static::getDb()->createCommand()->insert(self::tableName(), $data)->execute();
    }
}
