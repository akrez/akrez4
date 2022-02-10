<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string $receipt
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
    public $receipt_file;

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
            [['!receipt'], 'required'],
            [['receipt_file'], 'safe', 'on' => [self::SCENARIO_DEFAULT]],

        ];
    }

    public function upload()
    {
        $gallery = Gallery::uploadBase64($this->receipt_file, Gallery::TYPE_RECEIPT);
        if ($gallery->hasErrors()) {
            $this->addErrors(['receipt_file' => $gallery->getErrorSummary(true)]);
            return false;
        }
        $this->receipt = $gallery->name;
        return true;
    }

    public static function findPaymentQueryForApi($blogName, $customerId, $invoiceId = null)
    {
        return Payment::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId])
            ->andWhere(['invoice_id' => $invoiceId]);
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
     * Gets query for [[Receipt0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGallery()
    {
        return $this->hasOne(Gallery::class, ['name' => 'receipt']);
    }
}
