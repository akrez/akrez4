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

    public $created_date_from;
    public $user_agent_like;
    public $user_agent_not_like;

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
            [['is_ajax', 'response_http_code'], 'integer', 'on' => ['save']],
            [['blog_name', 'ip', 'controller', 'action', 'model_id', 'model_parent_id'], 'string', 'max' => 60, 'on' => ['save']],
            [['method'], 'string', 'max' => 11, 'on' => ['save']],
            [['created_date'], 'string', 'max' => 19, 'on' => ['save']],
            [['url', 'user_agent'], 'string', 'max' => 2047, 'on' => ['save']],
            [['data_post'], 'string', 'max' => 4096, 'on' => ['save']],
            //
            [['!blog_name', '!created_date_from', '!response_http_code'], 'safe'],
            [['action'], 'in', 'range' => ['search', 'product']],
            [['model_category_id'], 'integer'],
            [['user_agent_like', 'user_agent_not_like'], 'string'],
        ];
    }

    public function statQueryGrouped()
    {
        return $this->statQuery()
            ->select([
                'DATE(`created_date`) AS Ymd',
                'HOUR(`created_date`) AS H',
                'COUNT(`ip`) AS count',
            ])
            ->groupBy(['Ymd', 'H',]);
    }

    public function statQuery()
    {
        return self::find()
            ->where(['blog_name' => $this->blog_name])
            ->andFilterWhere(['>', 'created_date', $this->created_date_from])
            ->andFilterWhere(['=', 'response_http_code', $this->response_http_code])
            ->andFilterWhere(['=', 'action', $this->action])
            ->andFilterWhere(['=', 'model_category_id', $this->model_category_id])
            ->andFilterWhere(['LIKE', 'user_agent', $this->user_agent_like])
            ->andFilterWhere(['NOT LIKE', 'user_agent', $this->user_agent_not_like]);
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
