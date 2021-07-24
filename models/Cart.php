<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property float $price_initial
 * @property int $cnt
 * @property int|null $package_id
 * @property int|null $customer_id
 * @property string|null $blog_name
 * @property int|null $cache_parents_active_status
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Package $package
 */
class Cart extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cnt'], 'integer'],
            [['cnt'], 'required'],
        ];
    }

    public static function blogValidQuery($id = null)
    {
        return Cart::find()
            ->andWhere(['blog_name' => Yii::$app->user->getId(),])
            ->andFilterWhere(['id' => $id]);
    }

    public static function findCartQueryForApi($blogName, $customerId)
    {
        return Cart::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId]);
    }

    public static function findCartFullQueryForApi($blogName, $customerId)
    {
        return Cart::findCartQueryForApi($blogName, $customerId)
            ->andWhere(['cache_parents_active_status' => Status::STATUS_ACTIVE]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'updated_at' => $this->updated_at,
            'price_initial' => $this->price_initial,
            'cnt' => $this->cnt,
            'package_id' => $this->package_id,
            'customer_id' => $this->customer_id,
        ];
    }

    public function response()
    {
        return $this->toArray() + [
            'errors' => $this->errors,
        ];
    }

    /**
     * Gets query for [[BlogName]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlogName()
    {
        return $this->hasOne(Blog::class, ['name' => 'blog_name']);
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[Package]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['id' => 'package_id']);
    }
}