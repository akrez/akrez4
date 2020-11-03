<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property string $status
 * @property string $title
 * @property float|null $price_min
 * @property float|null $price_max
 * @property string|null $des
 * @property string|null $image
 * @property int|null $category_id
 * @property string|null $user_name
 *
 * @property Category $category
 * @property Gallery $gallery
 * @property User $user
 */
class Product extends ActiveRecord
{

    public $picture;

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

    public static function userValidQuery($id = null)
    {
        $query = Product::find();
        $query->andWhere(['status' => array_keys(Product::validStatuses())]);
        $query->andWhere(['user_name' => Yii::$app->user->getId()]);
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
            $product->user_name = Yii::$app->user->getIdentity()->name;
            $product->status = Status::STATUS_DISABLE;
            if ($product->save()) {
                $correctLines[] = $line;
            } else {
                $errorLines[] = $line;
                $errors = array_merge($errors, $product->getErrorSummary(true));
            }
        }
        if ($errors) {
            $textAreaModel->addErrors(['values' => $errors]);
            $textAreaModel->setValues($errorLines);
        }
        return $correctLines;
    }

    public function getPackages()
    {
        return $this->hasMany(Package::className(), ['product_id' => 'id']);
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
     * Gets query for [[UserName]].
     *
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['name' => 'user_name']);
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
