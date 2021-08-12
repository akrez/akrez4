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
 * @property string|null $name
 * @property string|null $mobile
 * @property string|null $phone
 * @property float $price
 * @property int $carts_count
 * @property string|null $params
 * @property string $blog_name
 * @property int $customer_id
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property InvoiceItem[] $invoiceItems
 */
class Invoice extends ActiveRecord
{
    public $postal_code;
    public $city;
    public $address;
    public $lat;
    public $lng;
    public $des;

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
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
            [['name', 'mobile', 'phone', 'postal_code', 'city', 'address', 'lat', 'lng',], 'required', 'on' => [self::SCENARIO_DEFAULT]],
            [['name'], 'string', 'max' => 60, 'on' => [self::SCENARIO_DEFAULT]],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9,15}$/', 'on' => [self::SCENARIO_DEFAULT]],
            [['phone',], 'match', 'pattern' => "/^0[0-9]{8,23}$/", 'on' => [self::SCENARIO_DEFAULT]],
            [['des'], 'string', 'on' => [self::SCENARIO_DEFAULT]],
            [['postal_code',], 'match', 'pattern' => "/^(\d{10})$/", 'on' => [self::SCENARIO_DEFAULT]],
            [['city'], 'in', 'range' => array_keys(City::getList()), 'on' => [self::SCENARIO_DEFAULT]],
            [['address'], 'string', 'on' => [self::SCENARIO_DEFAULT]],
            [['lat',], 'match', 'pattern' => "/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/", 'on' => [self::SCENARIO_DEFAULT]],
            [['lng',], 'match', 'pattern' => "/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/", 'on' => [self::SCENARIO_DEFAULT]],
            //
            [['price'], 'integer', 'min' => 0, 'on' => ['carts_count']],
            [['carts_count'], 'validCartsCount', 'on' => ['carts_count']],
        ];
    }

    public function validCartsCount($attribute, $params, $validator)
    {
        $this->carts_count = intval($this->carts_count);
        if ($this->carts_count <= 0) {
            $this->addError($attribute, Yii::t('app', 'You have no items in your shopping cart. Please add at least one product to cart!'));
        }
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
            //
            'price' => $this->price,
            'carts_count' => $this->carts_count,
        ];
    }
}
