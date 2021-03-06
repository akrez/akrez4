<?php

namespace app\models;

use app\components\Cache;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int|null $view
 * @property int $status
 * @property string $title
 * @property float|null $price_min
 * @property float|null $price_max
 * @property string|null $des
 * @property string|null $image
 * @property int|null $category_id
 * @property string|null $blog_name
 *
 * @property Category $category
 * @property Gallery $gallery
 * @property Blog $blog
 */
class Product extends ActiveRecord
{

    public $picture;
    public $cache_fields;

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }

    public static function tableName()
    {
        return 'product';
    }

    public function rules()
    {
        return [
            [['status', 'title', 'category_id'], 'required'],
            [['category_id'], 'integer'],
            [['status'], 'in', 'range' => array_keys(self::validStatuses())],
            [['title'], 'string', 'max' => 64],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['des'], 'string', 'max' => 160],
            [['picture',], 'file'],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'cache_fields' => [],
        ];

        $this->cache_fields = $arrayParams['cache_fields'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->params = [
            'cache_fields' => $this->cache_fields,
        ];

        $this->params = Json::encode($this->params);
        return true;
    }

    public static function blogValidQuery($id = null)
    {
        $query = Product::find();
        $query->andWhere(['status' => array_keys(Product::validStatuses())]);
        $query->andWhere(['blog_name' => Yii::$app->user->getId()]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public static function batchSave($textAreaModel, $categoryModel)
    {
        $correctLines = [];
        $errorLines = [];
        $errors = [];
        foreach ($textAreaModel->explodeLines() as $line) {
            $product = new Product();
            $product->title = $line;
            $product->category_id = $categoryModel->id;
            $product->blog_name = Yii::$app->user->getIdentity()->name;
            $product->status = Status::STATUS_ACTIVE;
            $product->view = 0;
            if ($product->save()) {
                $correctLines[] = $line;
            } else {
                $errorLines[] = $line;
                $errors = array_merge($errors, $product->getErrorSummary(true));
            }
        }
        if ($errors) {
            $textAreaModel->addErrors(['values' => $errors]);
        }
        $textAreaModel->setValues($errorLines);
        return $correctLines;
    }

    public function updatePrice()
    {
        $priceRange = (array) Package::blogValidQuery()
            ->select(['price_min' => 'MIN(price)', 'price_max' => 'MAX(price)'])
            ->where(['product_id' => $this->id])
            ->andWhere(['status' => Status::STATUS_ACTIVE])
            ->asArray()->one() + ['price_min' => null, 'price_max' => null];
        $this->price_min = ($priceRange['price_min'] === null ? null : doubleval($priceRange['price_min']));
        $this->price_max = ($priceRange['price_max'] === null ? null : doubleval($priceRange['price_max']));
        $this->save();
    }

    public function getPackages()
    {
        return $this->hasMany(Package::className(), ['product_id' => 'id']);
    }

    public static function findProductQueryForApi($blogName)
    {
        return Product::find()->where(['AND', ['blog_name' => $blogName, 'status' => Status::STATUS_ACTIVE,]]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'title' => $this->title,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'des' => $this->des,
            'view' => $this->view,
            'image' => $this->image,
            'category_id' => $this->category_id,
            '_fields' => Cache::getProductCacheField($this),
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Gallery]].
     *
     * @return ActiveQuery
     */
    public function getGalleries()
    {
        return $this->hasMany(Gallery::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[BlogName]].
     *
     * @return ActiveQuery
     */
    public function getBlog()
    {
        return $this->hasOne(Blog::className(), ['name' => 'blog_name']);
    }

    /**
     * Gets query for [[ProductFields]].
     *
     * @return ActiveQuery
     */
    public function getProductFields()
    {
        return $this->hasMany(ProductField::className(), ['product_id' => 'id']);
    }
}
