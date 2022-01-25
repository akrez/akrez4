<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "delivery".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int|null $deleted_at
 * @property int|null $status
 * @property string|null $name
 * @property string|null $mobile
 * @property string|null $phone
 * @property string|null $params
 * @property string $blog_name
 * @property int $customer_id
 * @property int|null $invoice_id
 * @property string|null $unique_hash
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Invoice $invoice
 */
class Delivery extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'delivery';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at', 'created_at', 'deleted_at', 'status', 'customer_id', 'invoice_id'], 'integer'],
            [['params'], 'string'],
            [['blog_name', 'customer_id'], 'required'],
            [['name', 'blog_name'], 'string', 'max' => 60],
            [['mobile'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 24],
            [['unique_hash'], 'string', 'max' => 32],
            [['deleted_at', 'customer_id', 'invoice_id'], 'unique', 'targetAttribute' => ['deleted_at', 'customer_id', 'invoice_id']],
            [['unique_hash'], 'unique'],
            [['blog_name'], 'exist', 'skipOnError' => true, 'targetClass' => Blog::class, 'targetAttribute' => ['blog_name' => 'name']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
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
