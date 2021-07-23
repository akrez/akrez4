<?php

namespace app\components;

use app\models\Basket;
use app\models\Blog;
use app\models\Category;
use app\models\Package;
use app\models\Product;
use app\models\ProductField;
use app\models\Status;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use app\models\Color;

class Cache extends Component
{
    public static function updateCategoryCacheOptions($category)
    {
        $product = Product::blogValidQuery()->select('id')->andWhere(['status' => Status::STATUS_ACTIVE])->andWhere(['category_id' => $category->id]);
        $categoryFields = ProductField::find()
            ->select(['field', 'value', 'cnt' => 'COUNT(`value`)',])
            ->andWhere(['product_id' => $product])
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

    public static function updateCachePages($entity, $page)
    {
        $entity->cache_has_page[$page->page_type] = ($page->status == Status::STATUS_ACTIVE);
        $entity->save();
    }

    public static function getCachePages($entity)
    {
        return (array) $entity->cache_has_page;
    }

    public static function updateBlogCacheCategory($blog)
    {
        $blog->cache_category = Category::findCategoryQueryForApi($blog->name)->select(['id', 'title'])->all();
        $blog->cache_category = ArrayHelper::map($blog->cache_category, 'id', 'title');
        $blog->save();
    }

    public static function updateBlogCacheColor($blog)
    {
        $blog->cache_color = Color::getList();
        $blog->save();
    }

    public static function getBlogCacheColor(Blog $blog)
    {
        return (array) $blog->cache_color;
    }

    public static function getBlogCacheColorLabel(Blog $blog, $item)
    {
        $list = self::getBlogCacheColor($blog);
        return (isset($list[$item]) ? $list[$item] : $item);
    }

    public static function getBlogCacheCategory($blog)
    {
        return isset($blog->cache_category) ? (array) $blog->cache_category : [];
    }

    public static function updateProductsCacheField($category)
    {
        foreach (Product::blogValidQuery()->andWhere(['category_id' => $category->id])->all() as  $product) {
            self::updateProductCacheField($product);
        }
    }

    public static function updateProductCacheField($product)
    {
        $query = 'SELECT `field`, `value`, `seq`, `in_summary`, `unit`
        FROM `product_field`
        LEFT JOIN `field` 
        ON `field`.`category_id` = `product_field`.`category_id` AND `field`.`title` = `product_field`.`field` AND `field`.`blog_name` = `product_field`.`blog_name`
        WHERE `product_field`.`category_id` = :category_id AND `product_field`.`product_id` = :product_id 
        ORDER BY `seq` DESC, `value` ASC, `field` ASC';

        $fields  = \Yii::$app->db->createCommand($query)
            ->bindValue(':product_id', $product->id)
            ->bindValue(':category_id', $product->category_id)
            ->queryAll();

        $cacheFields = [];
        foreach ($fields as $field) {
            if (!isset($cacheFields[$field['field']])) {
                $cacheFields[$field['field']] = [
                    'field' => $field['field'],
                    'values' => [],
                    'seq' => strval($field['seq']),
                    'in_summary' => (mb_strlen($field['in_summary']) && $field['in_summary'] == 0 ? "0" : "1"),
                    'unit' => strval($field['unit']),
                ];
            }
            $cacheFields[$field['field']]['values'][] = $field['value'];
        }

        $product->cache_fields = $cacheFields;
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

    public static function updateCacheParentsActiveStatus($entity)
    {
        //blogName
        if ($entity instanceof Blog) {
            $blogName = $entity->name;
        } else {
            $blogName = $entity->blog_name;
        }

        //
        $whereCategory = null;
        if ($entity instanceof Blog) {
            $whereBlog = Blog::blogValidQuery($blogName)->select('name');
            $whereCategory = Category::blogValidQuery()->select('id');
        }
        if ($whereCategory) {
            Category::updateAll(['cache_parents_active_status' => Status::STATUS_NOTACTIVE], ['id' => $whereCategory]);
            Category::updateAll(['cache_parents_active_status' => Status::STATUS_ACTIVE], [
                'AND',
                [
                    'id' => $whereCategory,
                    'blog_name' => $whereBlog->andWhere(['status' => Status::STATUS_ACTIVE,])
                ],
            ]);
        }
        //
        $whereProduct = null;
        if ($whereCategory) {
            $whereProduct = Product::blogValidQuery()->select('id')->andWhere(['category_id' => $whereCategory,]);
        } elseif ($entity instanceof Category) {
            $whereCategory = Category::blogValidQuery()->select('id')->andWhere(['id' => $entity->id,]);
            $whereProduct = Product::blogValidQuery()->select('id')->andWhere(['category_id' => $entity->id,]);
        }
        if ($whereProduct) {
            Product::updateAll(['cache_parents_active_status' => Status::STATUS_NOTACTIVE], ['id' => $whereProduct]);
            Product::updateAll(['cache_parents_active_status' => Status::STATUS_ACTIVE], [
                'AND',
                [
                    'id' => $whereProduct,
                    'category_id' => (clone $whereCategory)
                        ->andWhere(['status' => Status::STATUS_ACTIVE,])
                        ->andWhere(['cache_parents_active_status' => Status::STATUS_ACTIVE,])
                ],
            ]);
        }
        //
        $wherePackage = null;
        if ($whereProduct) {
            $wherePackage = Package::blogValidQuery()->select('id')->andWhere(['product_id' => $whereProduct,]);
        } elseif ($entity instanceof Product) {
            $whereProduct = Product::blogValidQuery()->select('id')->andWhere(['id' => $entity->id,]);
            $wherePackage = Package::blogValidQuery()->select('id')->andWhere(['product_id' => $entity->id,]);
        }
        if ($wherePackage) {
            Package::updateAll(['cache_parents_active_status' => Status::STATUS_NOTACTIVE], ['id' => $wherePackage]);
            Package::updateAll(['cache_parents_active_status' => Status::STATUS_ACTIVE], [
                'AND',
                [
                    'id' => $wherePackage,
                    'product_id' => (clone $whereProduct)
                    ->andWhere(['status' => Status::STATUS_ACTIVE,])
                    ->andWhere(['cache_parents_active_status' => Status::STATUS_ACTIVE,])
                ],
            ]);
        }
        //
        $whereBasket = null;
        if ($wherePackage) {
            $whereBasket = Basket::blogValidQuery()->select('id')->andWhere(['package_id' => $wherePackage,]);
        } elseif ($entity instanceof Package) {
            $wherePackage = Package::blogValidQuery()->select('id')->andWhere(['id' => $entity->id,]);
            $whereBasket = Basket::blogValidQuery()->select('id')->andWhere(['package_id' => $entity->id,]);
        }
        if ($whereBasket) {
            Basket::updateAll(['cache_parents_active_status' => Status::STATUS_NOTACTIVE], ['id' => $whereBasket]);
            Basket::updateAll(['cache_parents_active_status' => Status::STATUS_ACTIVE], [
                'AND',
                [
                    'id' => $whereBasket,
                    'package_id' => (clone $wherePackage)
                        ->andWhere(['status' => Status::STATUS_ACTIVE,])
                        ->andWhere(['cache_parents_active_status' => Status::STATUS_ACTIVE,])
                ],
            ]);
        }
    }
}
