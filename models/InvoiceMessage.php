<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoice_chat".
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
class InvoiceChat extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'invoice_id', 'is_customer'], 'integer'],
            [['message'], 'string'],
            [['blog_name'], 'string', 'max' => 31],
        ];
    }

    public static function createInvoiceChat($blogName, $invoiceId, $message, $isCustomer)
    {
        $invoiceChat = new InvoiceChat();
        $invoiceChat->blog_name = $blogName;
        $invoiceChat->invoice_id = $invoiceId;
        $invoiceChat->message = $message;
        $invoiceChat->is_customer = $isCustomer;
        $invoiceChat->save();
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'blog_name' => $this->blog_name,
            'invoice_id' => $this->invoice_id,
            'message' => $this->message,
            'is_customer' => $this->is_customer,
        ];
    }

    public static function findInvoiceChatQueryForApi($blogName, $invoiceId)
    {
        return InvoiceStatus::find()
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
