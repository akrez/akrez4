<?php

namespace app\models;

use app\components\Cache;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property string $title
 * @property int $status
 * @property string|null $params
 * @property string|null $blog_name
 *
 * @property Blog $blogName
 */
class Category extends ActiveRecord
{

    public $price_min;
    public $price_max;
    public $des;
    public $cache_options;
    public $cache_has_page;

    public static function tableName()
    {
        return 'category';
    }

    public function rules()
    {
        return [
            [['status'], 'in', 'range' => array_keys(self::validStatuses())],
            [['title', 'status'], 'required'],
            [['title'], 'filter', 'filter' => 'trim'],
            [['des'], 'string', 'max' => 160],
            [['title'], 'string', 'max' => 64],
            [['title'], 'unique', 'targetAttribute' => ['title', 'blog_name']],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        $arrayParams = (array) Json::decode($this->params) + [
            'price_min' => null,
            'price_max' => null,
            'des' => null,
            'cache_has_page' => [],
            'cache_options' => [],
        ];
        $this->price_min = $arrayParams['price_min'];
        $this->price_max = $arrayParams['price_max'];
        $this->des = $arrayParams['des'];
        $this->cache_has_page = $arrayParams['cache_has_page'];
        $this->cache_options = $arrayParams['cache_options'];
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
            'cache_has_page' => $this->cache_has_page,
            'cache_options' => $this->cache_options,
        ]);

        return true;
    }

    public static function blogValidQuery($id = null)
    {
        $query = Category::find();
        $query->andWhere(['blog_name' => Yii::$app->user->getId(),]);
        $query->andWhere(['status' => array_keys(Category::validStatuses())]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public function updatePrice()
    {
        $categoryProductQuery = Product::blogValidQuery()->select('id')->where(['category_id' => $this->id]);
        $categoryPriceRange = (array) PackageSearch::blogValidQuery()
            ->select(['price_min' => 'MIN(price)', 'price_max' => 'MAX(price)'])
            ->where(['product_id' => $categoryProductQuery])
            ->andWhere(['status' => Status::STATUS_ACTIVE])
            ->asArray()->one() + ['price_min' => null, 'price_max' => null];
        $this->price_min = ($categoryPriceRange['price_min'] === null ? null : doubleval($categoryPriceRange['price_min']));
        $this->price_max = ($categoryPriceRange['price_max'] === null ? null : doubleval($categoryPriceRange['price_max']));
        $this->save();
    }

    public static function batchSave($lines)
    {
        $errors = [];
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($lines as $line) {
            $category = new Category();
            $category->title = $line;
            $category->status = Status::STATUS_DISABLE;
            $category->blog_name = Yii::$app->user->getIdentity()->name;
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
        return CategorySearch::blogValidQuery()->select(['title', 'id'])->indexBy('id')->column();
    }

    public static function findCategoryForApi($blogName, $id)
    {
        return Category::find()->where(['AND', ['id' => $id, 'blog_name' => $blogName, 'status' => Status::STATUS_ACTIVE,]])->one();
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'title' => $this->title,
            'blog_name' => $this->blog_name,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'des' => $this->des,
            'has_page' => Cache::getCachePages($this),
        ];
    }

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }
}
