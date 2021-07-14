<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "basket".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property float $price
 * @property int $cnt
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

    public static function findBasketQueryForApi($blogName, $customerId)
    {
        return Basket::find()->where(['blog_name' => $blogName])->andWhere(['customer_id' => $customerId]);
    }

    public static $getBasketPackagesCache = [];
    public static function getBasketPackages($blogName, $packageIds)
    {
        if (!is_array($packageIds)) {
            $packageIds = [$packageIds];
        }

        $result = [];
        $cacheUpdated = false;
        foreach ($packageIds as $packageId) {
            if (!isset(self::$getBasketPackagesCache[$packageId])) {
                self::$getBasketPackagesCache[$packageId] = null;
                if (!$cacheUpdated) {
                    self::$getBasketPackagesCache = Package::findPackageFullQueryForApi($blogName)
                        ->andWhere(['id' => $packageIds])
                        ->indexBy('id')
                        ->all() + self::$getBasketPackagesCache;
                    $cacheUpdated = true;
                }
            }
            $result[$packageId] = self::$getBasketPackagesCache[$packageId];
        }

        return $result;
    }

    public function packageValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $packages = self::getBasketPackages($this->blog_name, $this->package_id);
            if (isset($packages[$this->package_id]) && $packages[$this->package_id]) {
                $this->_package = $packages[$this->package_id];
                if ($this->cnt <= $this->_package->cache_stock) {
                    $this->price = $this->package->price;
                } else {
                    $this->addError($attribute, Yii::t('app', 'Inventory left in stock is less than the specified amount'));
                }
            } else {
                $this->addError($attribute, Yii::t('app', 'Unfortunately the product is not available at the moment'));
            }
        }
    }

    public static function findDuplicateForApi($blogName, $customerId, $packageId)
    {
        return self::find()
            ->where(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId])
            ->andWhere(['package_id' => $packageId])
            ->one();
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'updated_at' => $this->updated_at,
            'price' => $this->price,
            'cnt' => $this->cnt,
            'package_id' => $this->package_id,
            'customer_id' => $this->customer_id,
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
