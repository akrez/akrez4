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

    public static function updateProductCacheCategoryStatus($categoryId, $newStatus)
    {
        Product::updateAll(['cache_category_status' => $newStatus], ['category_id' => $categoryId]);
    }

    public static function updateProductFieldCache($categoryId, $productId = null)
    {
        $command = \Yii::$app->db->createCommand('UPDATE
                `product_field`
            LEFT JOIN `field` ON `field`.`category_id` = `product_field`.`category_id` AND `field`.`title` = `product_field`.`field` AND `field`.`user_name` = `product_field`.`user_name`
            SET
                `cache_seq` = `field`.`seq`,
                `cache_in_summary` = `field`.`in_summary`
            WHERE
                `product_field`.`category_id` = :category_id ' . ($productId ? ' AND `product_field`.`product_id` = :product_id ' : ''))
            ->bindValue(':category_id', $categoryId);
        if ($productId) {
            $command->bindValue(':product_id', $productId);
        }
        $command->execute();
    }


    public static function updateUserCacheCategory($user)
    {
        $user->cache_category = Category::userValidQuery()->select(['id', 'title'])->where(['status' => Status::STATUS_ACTIVE])->all();
        $user->cache_category = ArrayHelper::map($user->cache_category, 'id', 'title');
        $user->save();
    }
}
