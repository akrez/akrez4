<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string $payment_name
 * @property int|null $invoice_id
 * @property string $blog_name
 * @property int $customer_id
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Gallery $gallery
 */
class Payment extends ActiveRecord
{
    public $payment_name_file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['!payment_name'], 'required'],
            [['payment_name_file'], 'safe', 'on' => [self::SCENARIO_DEFAULT]],

        ];
    }

    public function upload()
    {
        $gallery = Gallery::uploadBase64($this->payment_name_file, Gallery::TYPE_PAYMENT, null, [], true, $this->blog_name);
        if ($gallery->hasErrors()) {
            $this->addErrors(['payment_name_file' => $gallery->getErrorSummary(true)]);
            return false;
        }
        $this->payment_name = $gallery->name;
        return true;
    }

    public static function findPaymentQueryForApi($blogName, $customerId, $invoiceId)
    {
        return Payment::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId])
            ->andWhere(['invoice_id' => $invoiceId]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'blog_name' => $this->blog_name,
            'customer_id' => $this->customer_id,
            'created_at' => $this->created_at,
            'payment_name' => $this->payment_name,
            'invoice_id' => $this->invoice_id,
        ];
    }

    public function paymentResponse()
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
     * Gets query for [[Gallery]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGallery()
    {
        return $this->hasOne(Gallery::class, ['name' => 'payment_name']);
    }
}
