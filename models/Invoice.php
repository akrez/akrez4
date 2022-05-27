<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "invoice".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int|null $status
 * @property float $price
 * @property int $carts_count
 * @property int|null $parent_delivery_id
 * @property int|null $delivery_id
 * @property string|null $params
 * @property string $blog_name
 * @property int $customer_id
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Delivery[] $deliveries
 * @property Delivery $delivery
 * @property InvoiceItem[] $invoiceItems
 * @property Delivery $parentDelivery
 * @property Payment $payment
 * @property Payment[] $payments
 */
class Invoice extends ActiveRecord
{
    public $_delivery = null;
    public $_carts = [];
    public $_payments = [];
    public $des = '';

    const STATUS_PENDING = 0;
    const STATUS_ON_HOLD = 10;
    const STATUS_WAITING_FOR_PAYMENT = 15;
    const STATUS_WAITING_FOR_PAYMENT_VERIFICATION = 20;
    const STATUS_PROCESSING = 30;
    const STATUS_SHIPPED = 40;
    const STATUS_COMPLETED = 50;
    const STATUS_REFUNDED = 60;

    public static function validStatuses()
    {
        return [
            self::STATUS_PENDING => Yii::t('app', 'pending'),
            self::STATUS_ON_HOLD => Yii::t('app', 'on hold'),
            self::STATUS_WAITING_FOR_PAYMENT => Yii::t('app', 'waiting for payment'),
            self::STATUS_WAITING_FOR_PAYMENT_VERIFICATION => Yii::t('app', 'waiting for payment verification'),
            self::STATUS_PROCESSING => Yii::t('app', 'processing'),
            self::STATUS_SHIPPED => Yii::t('app', 'shipped'),
            self::STATUS_COMPLETED => Yii::t('app', 'completed'),
            self::STATUS_REFUNDED => Yii::t('app', 'refunded'),
        ];
    }

    public static function getLabel($item)
    {
        $list = self::validStatuses();
        return (isset($list[$item]) ? $list[$item] : $item);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['des'], 'string', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            //
            [['parent_delivery_id'], 'required', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            [['parent_delivery_id'], 'integer', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            [['parent_delivery_id'], 'deliveryValidation', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            //
            [['!carts_count'], 'cartsValidation', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            [['!price'], 'integer', 'min' => 0, 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            //
            [['!carts_count'], 'paymentValidation', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            /////
            [['!delivery_id'], 'required', 'skipOnEmpty' => false, 'on' => 'setDeliveryId'],
            [['!delivery_id'], 'integer', 'skipOnEmpty' => false, 'on' => 'setDeliveryId'],
            /////
            [['status'], 'in', 'range' => array_keys(Invoice::validStatuses()), 'on' => 'setStatus'],
        ];
    }

    public function paymentValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $payments = Payment::findPaymentQueryForApi($this->blog_name, $this->customer_id, null)->all();
            if ($payments) {
                return $this->_payments = $payments;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_payments = [];
    }

    public function cartsValidation($attribute, $params, $validator)
    {
        if (!$this->hasErrors()) {
            $carts = Cart::cartResponse($this->blog_name, $this->customer_id, true);
            if (intval($carts['carts_count']) > 0) {
                $this->price = $carts['price'];
                $this->carts_count = intval($carts['carts_count']);
                return $this->_carts = $carts;
            }
            $this->addError($attribute, Yii::t('app', 'You have no items in your shopping cart. Please add at least one product to cart!'));
        }
        return $this->_carts = [];
    }

    public function deliveryValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $delivery = Delivery::findDeliveryQueryForApi($this->blog_name, $this->customer_id)->one();
            if ($delivery) {
                return $this->_delivery = $delivery;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_delivery = null;
    }

    public function invoiceResponse()
    {
        return $this->toArray() + [
            'errors' => $this->errors,
        ];
    }

    public function invoiceFullResponse()
    {
        $data = [
            'invoice' => $this,
            'customer' => null,
            //
            'invoiceItems' => [],
            //
            'deliveries' => [],
            'payments' => [],
            //
            'invoiceStatuses' => [],
            'invoiceMessages' => [],
        ];

        if ($this->id) {
            $data['customer'] = Customer::findCustomerQueryForApiById($this->blog_name, $this->customer_id, Customer::validStatusesKey())
                ->one();
            $data['deliveries'] = Delivery::findDeliveryQueryForApi($this->blog_name, $this->customer_id, $this->id)
                ->orderBy(['id' => SORT_DESC])
                ->all();
            $data['payments'] = Payment::findPaymentQueryForApi($this->blog_name, $this->customer_id, $this->id)
                ->orderBy(['id' => SORT_DESC])
                ->all();
            $data['invoiceItems'] = InvoiceItem::findInvoiceItemQueryForApi($this->blog_name, $this->customer_id)
                ->andWhere(['invoice_id' => $this->id])
                ->all();
            $data['invoiceStatuses'] = InvoiceStatus::findInvoiceStatusQueryForApi($this->blog_name, $this->id)
                ->all();
            $data['invoiceMessages'] = InvoiceMessage::findInvoiceMessageQueryForApi($this->blog_name, $this->id)
                ->all();
        }

        $data['invoice'] = $data['invoice']->invoiceResponse();
        return ArrayHelper::toArray($data);
    }

    public static function findInvoiceQueryForApi($blogName, $customerId)
    {
        return Invoice::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId]);
    }

    public function setNewStatus($newStatus)
    {
        $result = false;
        $oldStatus = $this->status;
        if ($newStatus !== $oldStatus) {
            $this->setScenario('setStatus');
            $this->status = $newStatus;
            $result = $this->save();
            if ($result) {
                InvoiceStatus::createInvoiceStatus($this->blog_name, $this->id, $oldStatus, $newStatus);
            }
        }
        return $result;
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'des' => null,
        ];
        $this->des = $arrayParams['des'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'des' => $this->des,
        ];
        $this->params = Json::encode($this->params);
        return true;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'blog_name' => $this->blog_name,
            'customer_id' => $this->customer_id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'parent_delivery_id' => $this->parent_delivery_id,
            'delivery_id' => $this->delivery_id,
            'des' => $this->des,
            'price' => $this->price,
            'carts_count' => $this->carts_count,
        ];
    }

    public function saveDeliveryId($delivery)
    {
        $this->setScenario('setDeliveryId');
        $this->delivery_id = $delivery->id;
        return $this->save();
    }

    public static function blogValidQuery($id = null)
    {
        $query = Invoice::find();
        $query->andWhere(['blog_name' => Yii::$app->user->getId()]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
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
     * Gets query for [[InvoiceItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, ['invoice_id' => 'id']);
    }

    /**
     * Gets query for [[Delivery]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasOne(Delivery::class, ['id' => 'delivery_id']);
    }

    /**
     * Gets query for [[Deliveries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveries()
    {
        return $this->hasMany(Delivery::class, ['invoice_id' => 'id']);
    }


    /**
     * Gets query for [[ParentDelivery]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentDelivery()
    {
        return $this->hasOne(Delivery::class, ['id' => 'parent_delivery_id']);
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['invoice_id' => 'id']);
    }
}
