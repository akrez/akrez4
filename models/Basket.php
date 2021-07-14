<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "basket".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property string $status
 * @property float $price
 * @property int $cnt
 * @property int $product_id
 * @property int|null $invoice_id
 * @property int $customer_id
 * @property int $package_id
 * @property string|null $blog_name
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Package $package
 * @property Product $product
 */
class Basket extends ActiveRecord
{
    public $_package;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'basket';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cnt'], 'integer', 'min' => 1],
            [['package_id'], 'integer'],
            [['cnt', 'package_id'], 'required'],
            [['package_id'], 'packageValidation'],
        ];
    }

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }

    public static function findBasketQueryForApi($blogName)
    {
        return Basket::find()->where(['AND', ['blog_name' => $blogName, 'status' => Status::STATUS_ACTIVE,]]);
    }

    public function packageValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_package = Package::findPackageFullQueryForApi($this->blog_name)
                ->andWhere(['id' => $this->package_id])
                ->one();
            if ($this->_package) {
                $this->product_id = $this->package->product_id;
                $this->price = $this->package->price;
            } else {
                $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
            }
        }
    }

    public static function findDuplicateForApi($blogName, $customerId, $packageId)
    {
        return self::find()
            ->where(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId])
            ->andWhere(['package_id' => $packageId])
            ->andWhere(['invoice_id' => null])
            ->one();
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'price' => $this->price,
            'cnt' => $this->cnt,
            'product_id' => $this->product_id,
            'package_id' => $this->package_id,
            'customer_id' => $this->customer_id,
            'invoice_id' => $this->invoice_id,
        ];
    }

    public function response()
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
     * Gets query for [[Package]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['id' => 'package_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
