<?php

namespace app\models;

use app\components\Cache;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "invoice_item".
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
 * @property int|null $invoice_id
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Invoice $invoice
 */
class InvoiceItem extends ActiveRecord
{
    public $cache_fields;
    public $guaranty;
    public $des;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_item';
    }

    public static function forge($invoice, $cart, $package, $product)
    {
        $invoiceItem = new InvoiceItem();

        $invoiceItem->customer_id = $invoice->customer_id;
        $invoiceItem->blog_name = $invoice->blog_name;
        $invoiceItem->invoice_id = $invoice->id;

        $invoiceItem->cnt = $cart->cnt;

        $invoiceItem->price = $package->price;
        $invoiceItem->color_code = $package->color_code;
        $invoiceItem->package_id = $package->id;

        $invoiceItem->title = $product->title;
        $invoiceItem->code = $product->code;
        $invoiceItem->image = $product->image;
        $invoiceItem->product_id = $product->id;
        $invoiceItem->category_id = $product->category_id;

        $invoiceItem->params = json_encode([
            'cache_fields' => Cache::getProductCacheField($product),
            'guaranty' => $package->guaranty,
            'des' => $package->des,
        ]);

        return $invoiceItem;
    }

    public static function findInvoiceItemQueryForApi($blogName, $customerId)
    {
        return InvoiceItem::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId]);
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'cache_fields' => [],
            'guaranty' => '',
            'des' => '',
        ];

        $this->cache_fields = $arrayParams['cache_fields'];
        $this->guaranty = $arrayParams['guaranty'];
        $this->des = $arrayParams['des'];
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'title' => $this->title,
            'code' => $this->code,
            'image' => $this->image,
            'price' => $this->price,
            'color_code' => $this->color_code,
            'cnt' => $this->cnt,
            'package_id' => $this->package_id,
            'product_id' => $this->product_id,
            'customer_id' => $this->customer_id,
            'category_id' => $this->category_id,
            'blog_name' => $this->blog_name,
            'invoice_id' => $this->invoice_id,
            //
            '_fields' => Cache::getProductCacheField($this),
            'guaranty' =>  $this->guaranty,
            'des' =>  $this->des,
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
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }
}
