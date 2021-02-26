<?php

namespace app\components;

use app\models\Category;
use app\models\Product;
use app\models\ProductField;
use app\models\Status;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class Cache extends Component
{
    public static function updateCategoryCacheOptions($category)
    {
        $product = Product::userValidQuery()->select('id')->where(['status' => Status::STATUS_ACTIVE])->andWhere(['category_id' => $category->id]);
        $categoryFields = ProductField::find()
            ->select(['field', 'value', 'cnt' => 'COUNT(`value`)',])
            ->where(['product_id' => $product])
            ->groupBy(['field', 'value',])
            ->orderBy(['cnt' => SORT_DESC])
            ->all();
        $category->cache_options = [];
        foreach ($categoryFields as $categoryField) {
            $category->cache_options[$categoryField['field']][$categoryField['value']] = $categoryField['cnt'];
        }
        $category->save();
    }

    public static function getCategoryCacheOptions($category)
    {
        return isset($category->cache_options) ? (array) $category->cache_options : [];
    }

    public static function updateUserCacheCategory($user)
    {
        $user->cache_category = Category::userValidQuery()->select(['id', 'title'])->where(['status' => Status::STATUS_ACTIVE])->all();
        $user->cache_category = ArrayHelper::map($user->cache_category, 'id', 'title');
        $user->save();
    }

    public static function getUserCacheCategory($user)
    {
        return isset($user->cache_category) ? (array) $user->cache_category : [];
    }

    public static function updateProductsCacheField($category)
    {
        foreach (Product::userValidQuery()->where(['category_id' => $category->id])->all() as  $product) {
            self::updateProductCacheField($product);
        }
    }

    public static function updateProductCacheField($product)
    {
        $query = 'SELECT `field`, `value`, `seq`, `in_summary`, `unit`
        FROM `product_field`
        LEFT JOIN `field` 
        ON `field`.`category_id` = `product_field`.`category_id` AND `field`.`title` = `product_field`.`field` AND `field`.`user_name` = `product_field`.`user_name`
        WHERE `product_field`.`category_id` = :category_id AND `product_field`.`product_id` = :product_id 
        ORDER BY `seq` DESC';
        $product->cache_fields  = \Yii::$app->db->createCommand($query)
            ->bindValue(':product_id', $product->id)
            ->bindValue(':category_id', $product->category_id)
            ->queryAll();
        $product->save();
    }

    public static function getProductCacheField($product)
    {
        return isset($product->cache_fields) ? (array) $product->cache_fields : [];
    }

    public static function updateProductPrice($product)
    {
        $product->updatePrice();
    }
    public static function updateCategoryPrice($category)
    {
        $category->updatePrice();
    }
}
