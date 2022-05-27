<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoice_message".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $invoice_id
 * @property string|null $message
 * @property int|null $is_customer
 * @property string|null $blog_name
 *
 * @property Blog $blogName
 * @property Invoice $invoice
 */
class InvoiceMessage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'invoice_id'], 'integer'],
            [['message'], 'safe'],
            [['blog_name'], 'string', 'max' => 31],
            //
            [['message'], 'required'],
            [['is_customer'], 'boolean'],
        ];
    }

    public function invoiceMessageResponse()
    {
        return $this->toArray() + [
            'errors' => $this->errors,
        ];
    }

    public static function createInvoiceMessage($blogName, $invoiceId, $message, $isCustomer)
    {
        $invoiceMessage = new InvoiceMessage();
        $invoiceMessage->blog_name = $blogName;
        $invoiceMessage->invoice_id = $invoiceId;
        $invoiceMessage->message = $message;
        $invoiceMessage->is_customer = $isCustomer;
        $invoiceMessage->save();
        return $invoiceMessage;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'blog_name' => $this->blog_name,
            'invoice_id' => $this->invoice_id,
            'message' => $this->message,
            'is_customer' => $this->is_customer,
            'created_at' => $this->created_at,
        ];
    }

    public static function findInvoiceMessageQueryForApi($blogName, $invoiceId)
    {
        return InvoiceMessage::find()
            ->andWhere(['blog_name' => $blogName])
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
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }
}
