<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property float $price_initial
 * @property int $cnt
 * @property int|null $package_id
 * @property int|null $customer_id
 * @property string|null $blog_name
 * @property int $product_id
 * @property int|null $cache_parents_active_status
 *
 * @property Blog $blogName
 * @property Customer $customer
 * @property Package $package
 * @property Product $product
 */
class Cart extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cnt'], 'integer'],
            [['cnt'], 'required'],
        ];
    }

    public static function blogValidQuery($id = null)
    {
        return Cart::find()
            ->andWhere(['blog_name' => Yii::$app->user->getId(),])
            ->andFilterWhere(['id' => $id]);
    }

    public static function findCartQueryForApi($blogName, $customerId)
    {
        return Cart::find()
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['customer_id' => $customerId]);
    }

    public static function findCartFullQueryForApi($blogName, $customerId)
    {
        return Cart::findCartQueryForApi($blogName, $customerId)
            ->andWhere(['cache_parents_active_status' => Status::STATUS_ACTIVE]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'updated_at' => $this->updated_at,
            'price_initial' => $this->price_initial,
            'cnt' => $this->cnt,
            'package_id' => $this->package_id,
            'customer_id' => $this->customer_id,
        ];
    }

    public static function cartResponse($blogName, $customerId, $asCardModel = false)
    {
        $price = 0;
        $carts_count = 0;
        $carts = [];
        $packages = [];
        $products = [];
        //
        $cartQuery = Cart::findCartFullQueryForApi($blogName, $customerId);
        $cartModels = $cartQuery->all();
        //
        if ($cartModels) {
            $packagesQuery = Package::findPackageFullQueryForApi($blogName)->where(['id' => (clone $cartQuery)->select('package_id')]);
            $packages = $packagesQuery->indexBy('id')->all();
            //
            $productsQuery = Product::findProductFullQueryForApi($blogName)->where(['id' => (clone $packagesQuery)->select('product_id')]);
            $products = $productsQuery->indexBy('id')->all();
            //
            foreach ($cartModels as $cartModel) {
                $package = $packages[$cartModel->package_id];
                $carts[$cartModel->id] = $cartModel;
                $carts[$cartModel->id]->packageValidation($package);
                if (!$cartModel->errors) {
                    $price = $price + ($package->price * $cartModel->cnt);
                    $carts_count += $cartModel->cnt;
                }

                if (!$asCardModel) {
                    $carts[$cartModel->id] = Cart::packageValidationResponse($cartModel, $package);
                }
            }
        }
        //
        return [
            'price' => $price,
            'carts_count' => $carts_count,
            'carts' => $carts,
            'packages' => $packages,
            'products' => $products,
        ];
    }

    public function packageValidation($package)
    {
        if ($package->check_stock) {
            if ($package->cache_stock <= 0) {
                $this->addError('cnt', Yii::t('app', 'Unfortunately the product is not available at the moment'));
            } elseif ($package->cache_stock < $this->cnt) {
                $this->addError('cnt', Yii::t('app', 'Inventory left in stock is less than the specified amount'));
            }
        }
        if (strlen($package->max_per_cart) && $package->max_per_cart < $this->cnt) {
            $this->addError('cnt', Yii::t('app', 'The amount is more than the maximum amount specified for each cart'));
        }
    }

    public static function packageValidationResponse($cart, $package)
    {
        return $cart->toArray() + [
            'errors' => $cart->errors,
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
