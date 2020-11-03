<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property string $title
 * @property string|null $params
 * @property string|null $user_name
 *
 * @property User $userName
 */
class Category extends ActiveRecord
{

    public $price_min;
    public $price_max;
    public $des;
    public $garanties;

    public static function tableName()
    {
        return 'category';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'filter', 'filter' => 'trim'],
            [['des'], 'string', 'max' => 160],
            [['title'], 'string', 'max' => 64],
            [['title'], 'unique', 'targetAttribute' => ['title', 'user_name']],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        $arrayParams = (array) Json::decode($this->params) + [
            'price_min' => null,
            'price_max' => null,
            'des' => null,
            'garanties' => [],
        ];
        $this->price_min = $arrayParams['price_min'];
        $this->price_max = $arrayParams['price_max'];
        $this->des = $arrayParams['des'];
        $this->garanties = $arrayParams['garanties'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->params = Json::encode([
                    'price_min' => $this->price_min,
                    'price_max' => $this->price_max,
                    'des' => $this->des,
                    'garanties' => $this->garanties,
        ]);

        return true;
    }

    public static function userValidQuery($id = null)
    {
        $query = Category::find();
        $query->andWhere(['user_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    /**
     *
     * @return array errors
     */
    public static function batchSave($lines)
    {
        $errors = [];
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($lines as $line) {
            $category = new Category();
            $category->title = $line;
            $category->user_name = Yii::$app->user->getIdentity()->name;
            if (!$category->save()) {
                $errors = array_merge($errors, $category->getErrorSummary(true));
            }
        }
        if ($errors) {
            $transaction->rollBack();
        } else {
            $transaction->commit();
        }
        return $errors;
    }

    public function getCategoriesList()
    {
        return CategorySearch::userValidQuery()->select(['title', 'id'])->indexBy('id')->column();
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'title' => $this->title,
            'user_name' => $this->user_name,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'des' => $this->des,
        ];
    }

}
