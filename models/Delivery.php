<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "delivery".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int|null $status
 * @property string|null $name
 * @property string|null $mobile
 * @property string|null $phone
 * @property string|null $params
 * @property string $blog_name
 * @property int $customer_id
 * @property int|null $invoice_id
 * @property int|null $parent_id
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Delivery[] $deliveries
 * @property Invoice $invoice
 * @property Invoice[] $invoices
 * @property Delivery $parent
 */
class Delivery extends ActiveRecord
{
    public $postal_code;
    public $city;
    public $address;
    public $lat;
    public $lng;
    public $des;

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
            [['name', 'mobile', 'phone', 'postal_code', 'city', 'address', 'lat', 'lng'], 'required'],
            [['lat',], 'match', 'pattern' => "/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/"],
            [['lng',], 'match', 'pattern' => "/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/"],
            [['name'], 'string', 'max' => 60],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9,15}$/'],
            [['phone',], 'match', 'pattern' => "/^0[0-9]{8,23}$/"],
            [['des'], 'string'],
            [['city'], 'in', 'range' => array_keys(City::getList())],
            [['postal_code',], 'match', 'pattern' => "/^(\d{10})$/"],
            [['address'], 'string'],
        ];
    }

    /**
     * @return self
     */
    public static function clone(Delivery $parentDelivery, $invoiceId)
    {
        $delivery = clone $parentDelivery;
        //
        $delivery->isNewRecord = true;
        $delivery->id = null;
        //
        $delivery->invoice_id = $invoiceId;
        $delivery->parent_id = $parentDelivery->id;
        //
        $delivery->save();
        //
        return $delivery;
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'postal_code' => null,
            'city' => null,
            'address' => null,
            'lat' => null,
            'lng' => null,
            'des' => null,
        ];
        $this->postal_code = $arrayParams['postal_code'];
        $this->city = $arrayParams['city'];
        $this->address = $arrayParams['address'];
        $this->lat = $arrayParams['lat'];
        $this->lng = $arrayParams['lng'];
        $this->des = $arrayParams['des'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'des' => $this->des,
        ];
        $this->params = Json::encode($this->params);
        return true;
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
     * Gets query for [[Deliveries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveries()
    {
        return $this->hasMany(Delivery::class, ['parent_id' => 'id']);
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

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Delivery::class, ['id' => 'parent_id']);
    }

    public function deliveryResponse()
    {
        return $this->toArray() + [
            'errors' => $this->errors,
        ];
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
            'name' => $this->name,
            'mobile' => $this->mobile,
            'phone' => $this->phone,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'des' => $this->des,
        ];
    }

    public static function storeAsTemplate(Delivery &$delivery, $post, $blog, $customer)
    {
        $delivery->load($post, '');
        $delivery->blog_name = $blog->name;
        $delivery->customer_id = $customer->id;
        $delivery->invoice_id = null;
        $delivery->save();
    }

    public static function findDeliveryQueryForApi($blogName, $customerId, $invoiceId = null)
    {
        return Delivery::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId])
            ->andWhere(['invoice_id' => $invoiceId]);
    }
}
