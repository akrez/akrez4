<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "invoice_status".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int $invoice_id
 * @property int|null $old_status
 * @property int $new_status
 *
 * @property Invoice $invoice
 */
class InvoiceStatus extends ActiveRecord
{
    public $old_status_text = '';
    public $new_status_text = '';
    public $message = '';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'invoice_id', 'old_status', 'new_status'], 'integer'],
        ];
    }

    public static function createInvoiceStatus($blogName, $invoiceId, $oldStatus, $newStatus)
    {
        $invoiceStatus = new InvoiceStatus();
        $invoiceStatus->blog_name = $blogName;
        $invoiceStatus->invoice_id = $invoiceId;
        $invoiceStatus->old_status = $oldStatus;
        $invoiceStatus->new_status = $newStatus;
        $invoiceStatus->save();
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->old_status_text = Yii::$app->formatter->asInvoiceStatus($this->old_status);
        $this->new_status_text = Yii::$app->formatter->asInvoiceStatus($this->new_status);
        if (mb_strlen($this->old_status)) {
            $this->message = Yii::t('app', 'ivoiceStatusChange', [
                'old_status' => Yii::$app->formatter->asInvoiceStatus($this->old_status),
                'new_status' => Yii::$app->formatter->asInvoiceStatus($this->new_status),
            ]);
        } else {
            $this->message = Yii::t('app', 'ivoiceStatusInit', [
                'new_status' => Yii::$app->formatter->asInvoiceStatus($this->new_status),
            ]);
        }
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'invoice_id' => $this->invoice_id,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'old_status_text' => $this->old_status_text,
            'new_status_text' => $this->new_status_text,
            'message' => $this->message,
        ];
    }

    public static function findInvoiceStatusQueryForApi($blogName, $invoiceId)
    {
        return InvoiceStatus::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['invoice_id' => $invoiceId]);
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
