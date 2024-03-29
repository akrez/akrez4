<?php

namespace app\models;

use app\components\Cache;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * This is the model class for table "package".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property string $status
 * @property double $price
 * @property string $guaranty
 * @property string|null $color_code
 * @property int|null $cache_stock
 * @property int|null $max_per_cart
 * @property int|null $check_stock
 * @property string $des
 * @property int $product_id
 * @property string|null $blog_name
 *
 * @property Product $product
 * @property Blog $blogName
 */
class Package extends ActiveRecord
{
    const MAX_IN_CART = 20;

    public $guaranty;
    public $des;
    //
    public $price_min;
    public $price_max;

    const CHECK_STOCK_NO = 0;
    const CHECK_STOCK_YES = 1;

    public static function checkStockList()
    {
        return [
            self::CHECK_STOCK_NO => Yii::t('yii', 'No'),
            self::CHECK_STOCK_YES => Yii::t('yii', 'Yes'),
        ];
    }

    public static function getCheckStockLabel($item)
    {
        $list = self::checkStockList();
        return (isset($list[$item]) ? $list[$item] : null);
    }

    public static function tableName()
    {
        return 'package';
    }

    public function rules()
    {
        return [
            [['status'], 'in', 'range' => array_keys(self::validStatuses())],
            [['color_code'], 'in', 'range' => array_keys(Cache::getBlogCacheColor(Yii::$app->user->getIdentity()))],
            [['price', 'status', 'guaranty'], 'required'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9,]*[.]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['cache_stock'], 'integer'],
            [['max_per_cart'], 'integer', 'min' => 0],
            [['check_stock'], 'in', 'range' => array_keys(self::checkStockList())],
            [['guaranty', 'des'], 'safe'],
        ];
    }

    public static function findPackageQueryForApi($blogName)
    {
        return Package::find()->where(['blog_name' => $blogName])->andWhere(['status' => Status::STATUS_ACTIVE]);
    }

    public static function findPackageFullQueryForApi($blogName)
    {
        return Package::findPackageQueryForApi($blogName)->andWhere(['cache_parents_active_status' => Status::STATUS_ACTIVE]);
    }

    public static function printHtmlForTelegram($package, $seprator)
    {
        $caption = [];
        if ($package->guaranty) {
            $caption[] = $package->guaranty;
        }
        if ($package->des) {
            $caption[] = $package->des;
        }
        if ($package->color_code) {
            $caption[] = Cache::getBlogCacheColorLabel(Yii::$app->user->getIdentity(), $package->color_code);
        }
        $caption[] = '<b>' . Yii::$app->formatter->asPrice($package->price) . '</b>';

        return implode($seprator, $caption);
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'guaranty' => null,
            'des' => null,
        ];
        $this->guaranty = $arrayParams['guaranty'];
        $this->des = $arrayParams['des'];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        $this->price = str_replace(',', '', $this->price);
        return true;
    }
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'guaranty' => $this->guaranty,
            'des' => $this->des,
        ];
        $this->params = Json::encode($this->params);
        return true;
    }

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }

    public static function blogValidQuery($id = null)
    {
        $query = Package::find();
        $query->andWhere(['status' => array_keys(Package::validStatuses())]);
        $query->andWhere(['blog_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public static function calcMaxInCart($package)
    {
        $mins = [self::MAX_IN_CART];
        if ($package->check_stock) {
            $mins[] = $package->cache_stock;
        }
        if (strlen($package->max_per_cart)) {
            $mins[] = $package->max_per_cart;
        }
        $min = min($mins);
        return ($min > 0 ? $min : 0);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'max_in_cart' => self::calcMaxInCart($this),
            'price' => $this->price,
            'product_id' => $this->product_id,
            'blog_name' => $this->blog_name,
            'guaranty' => $this->guaranty,
            'color_code' => $this->color_code,
            'des' => $this->des,
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[BlogName]].
     *
     * @return ActiveQuery
     */
    public function getBlogName()
    {
        return $this->hasOne(Blog::class, ['name' => 'blog_name']);
    }
}
