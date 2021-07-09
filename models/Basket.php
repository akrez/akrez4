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
 * @property float $package_price
 * @property int $cnt
 * @property string|null $color_code
 * @property int $product_id
 * @property int $customer_id
 * @property int $invoice_id
 * @property string|null $blog_name
 * @property string|null $params
 *
 * @property Blog $blogName
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
            [['cnt',], 'integer', 'min' => 1],
            [['!status', '!price', '!package_price', 'cnt', '!product_id', '!customer_id'], 'required'],
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
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id'])->inverseOf('baskets');
    }
}
