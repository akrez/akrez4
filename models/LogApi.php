<?php

namespace app\models;

use app\components\Helper;
use app\components\Jdf;
use app\controllers\Api;
use Yii;

/**
 * This is the model class for table "log_api".
 *
 * @property int $id
 * @property string|null $blog_name
 * @property string|null $ip
 * @property string|null $method
 * @property int|null $is_ajax
 * @property string|null $url
 * @property int|null $response_http_code
 * @property string|null $created_date
 * @property string|null $data_post
 * @property string|null $user_agent
 * @property string|null $controller
 * @property string|null $action
 * @property string|null $model_id
 * @property int|null $customer_id
 * @property int|null $model_category_id
 * @property string|null $model_parent_id
 */
class LogApi extends Log
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_api';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_ajax', 'response_http_code'], 'integer'],
            [['blog_name', 'ip', 'controller', 'action', 'model_id', 'model_parent_id'], 'string', 'max' => 60],
            [['method'], 'string', 'max' => 11],
            [['created_date'], 'string', 'max' => 19],
            [['url', 'user_agent'], 'string', 'max' => 2047],
            [['data_post'], 'string', 'max' => 4096],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function log($params = [])
    {
        $template = $params + [
            'blog_name' => Yii::$app->request->get(Api::BLOG_PARAM),
            'ip' => (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : Yii::$app->request->getUserIP()),
            'method' => Yii::$app->request->method,
            'is_ajax' => Yii::$app->request->isAjax,
            'url' => $_SERVER['REQUEST_URI'],
            'response_http_code' => Yii::$app->response->statusCode,
            'created_date' => Jdf::jdate('Y-m-d H:i:s'),
            'data_post' => json_encode(Yii::$app->request->post()),
            'user_agent' => Yii::$app->request->getUserAgent(),
            'controller' => Yii::$app->controller->id,
            'action' => Yii::$app->controller->action->id,
            'model_id' => Yii::$app->request->get('id'),
            'customer_id' => Yii::$app->customerApi->getId(),
            'model_category_id' => Yii::$app->request->get('model_category_id'),
            'model_parent_id' => Yii::$app->request->get('parent_id'),
        ];
        $data = Helper::templatedArray($template, $params);
        return static::getDb()->createCommand()->insert(self::tableName(), $data)->execute();
    }
}
