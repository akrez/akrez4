<?php

namespace app\models;

use app\components\Cache;
use Yii;

/**
 * This is the model class for table "order_item".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string $title
 * @property string|null $code
 * @property string|null $image
 * @property float $price
 * @property string|null $color_code
 * @property int $cnt
 * @property string|null $params
 * @property int|null $package_id
 * @property int $product_id
 * @property int|null $customer_id
 * @property int|null $category_id
 * @property string|null $blog_name
 * @property int|null $order_id
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Order $order
 */
class OrderItem extends ActiveRecord
{
    public $cache_fields;
    public $guaranty;
    public $des;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_item';
    }

    public static function forge($order, $cart, $package, $product)
    {
        $orderItem = new OrderItem();

        $orderItem->customer_id = $order->customer_id;
        $orderItem->blog_name = $order->blog_name;
        $orderItem->order_id = $order->id;

        $orderItem->cnt = $cart->cnt;

        $orderItem->price = $package->price;
        $orderItem->color_code = $package->color_code;
        $orderItem->package_id = $package->id;

        $orderItem->title = $product->title;
        $orderItem->code = $product->code;
        $orderItem->image = $product->image;
        $orderItem->product_id = $product->id;
        $orderItem->category_id = $product->category_id;

        $orderItem->params = json_encode([
            'cache_fields' => Cache::getProductCacheField($product),
            'guaranty' => $package->guaranty,
            'des' => $package->des,
        ]);

        return $orderItem;
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
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
