<?php

namespace app\models;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string $receipt
 * @property int|null $invoice_id
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
     * Gets query for [[Gallery]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGallery()
    {
        return $this->hasOne(Gallery::class, ['name' => 'receipt']);
    }
}
