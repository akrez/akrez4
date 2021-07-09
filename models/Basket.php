<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "basket".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property string $status
 * @property float $price
 * @property int $cnt
 * @property int $product_id
 * @property int $customer_id
 * @property int|null $package_id
 * @property string|null $blog_name
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Product $product
 */
class Basket extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'basket';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at', 'cnt', 'product_id', 'package_id', 'customer_id'], 'integer'],
            [['status', 'price', 'cnt', 'product_id', 'customer_id'], 'required'],
            [['price'], 'number'],
            [['status'], 'string', 'max' => 12],
            [['blog_name'], 'string', 'max' => 31],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['blog_name'], 'exist', 'skipOnError' => true, 'targetClass' => Blog::class, 'targetAttribute' => ['blog_name' => 'name']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * Gets query for [[BlogName]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlogName()
    {
        return $this->hasOne(Blog::class, ['name' => 'blog_name'])->inverseOf('baskets');
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id'])->inverseOf('baskets');
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id'])->inverseOf('baskets');
    }
}
