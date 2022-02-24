<?php

namespace app\models;

use Yii;
use app\models\City;
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
 * @property int|null $delivery_at
 * @property int|null $payment_id
 * @property int|null $payment_at
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
    public $_delivery;
    public $_carts;
    public $_payments;
    public $des;

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
            [['!payment_id'], 'paymentValidation', 'skipOnEmpty' => false, 'on' => self::SCENARIO_DEFAULT],
            /////
            [['!delivery_id'], 'required', 'skipOnEmpty' => false, 'on' => 'setDeliveryId'],
            [['!delivery_id'], 'integer', 'skipOnEmpty' => false, 'on' => 'setDeliveryId'],
        ];
    }

    public function paymentValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $payments = Payment::findPaymentQueryForApi($this->blog_name, $this->customer_id, null)->orderBy(['created_at' => SORT_DESC])->all();
            if ($payments) {
                $lastPayment = reset($payments);
                $this->payment_id = $lastPayment->id;
                return $this->_payments = $payments;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_payments = null;
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
        return $this->_payments = null;
    }

    public function deliveryValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $delivery = Delivery::findDeliveryQueryForApi($this->blog_name, $this->customer_id, true)->one();
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

    public static function findInvoiceQueryForApi($blogName, $customerId)
    {
        return Invoice::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId]);
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
            'delivery_at' => $this->delivery_at,
            'payment_id' => $this->payment_id,
            'payment_at' => $this->payment_at,
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
     * Gets query for [[Payment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['id' => 'payment_id']);
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
