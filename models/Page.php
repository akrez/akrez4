<?php

namespace app\models;

use Yii;
use app\models\Category;
use app\models\Product;

/**
 * This is the model class for table "page".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int $status
 * @property string|null $body
 * @property string $entity
 * @property string $entity_id
 * @property string|null $blog_name
 *
 * @property Blog $blogName
 */
class Page extends ActiveRecord
{
    const ENTITY_BLOG = 'Blog';
    const ENTITY_CATEGORY = 'Category';
    const ENTITY_PRODUCT = 'Product';

    const ENTITY_BLOG_INDEX = 'Index';
    const ENTITY_BLOG_CONTACTUS = 'Contact Us';
    const ENTITY_BLOG_ABOUTUS = 'About Us';

    public static function entityList()
    {
        return [
            self::ENTITY_BLOG => Yii::t('app', 'Blog'),
            self::ENTITY_CATEGORY => Yii::t('app', 'Category'),
            self::ENTITY_PRODUCT => Yii::t('app', 'Product'),
        ];
    }

    public static function entityBlogList()
    {
        return [
            self::ENTITY_BLOG_INDEX => Yii::t('app', 'Index'),
            self::ENTITY_BLOG_CONTACTUS => Yii::t('app', 'Contact Us'),
            self::ENTITY_BLOG_ABOUTUS => Yii::t('app', 'About Us'),
        ];
    }

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }

    public static function tableName()
    {
        return 'page';
    }

    public function rules()
    {
        return [
            [['!entity'], 'entityFilter'],
            [['status', '!entity', '!entity_id'], 'required'],
            [['status'], 'in', 'range' => array_keys(self::validStatuses())],
            [['body'], 'string'],
        ];
    }

    public static function blogValidQuery($id = null)
    {
        $query = Page::find();
        $query->andWhere(['blog_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public function entityFilter($attribute, $params, $validator)
    {
        $this->setEntity($this->entity, $this->entity_id);
    }

    //in case of error return false
    private $entityModelsCache = [];
    public function setEntity($entity, $entityId)
    {
        $cacheKey = $entity . '-' . $entityId;
        if (!isset($this->entityModelsCache[$cacheKey])) {
            $entityModel = false;
            if ($entity == self::ENTITY_BLOG) {
                $list = self::entityBlogList();
                if (isset($list[$entityId])) {
                    $entityModel = null;
                }
            } else if ($entity == self::ENTITY_CATEGORY) {
                $entityModel = Category::blogValidQuery($entityId)->one();
                if (empty($entityModel)) {
                    $entityModel = false;
                }
            } elseif ($entity == self::ENTITY_PRODUCT) {
                $entityModel = Product::blogValidQuery($entityId)->one();
                if (empty($entityModel)) {
                    $entityModel = false;
                }
            }
            $this->entityModelsCache[$cacheKey] = $entityModel;
        }

        if ($this->entityModelsCache[$cacheKey] === false) {
            $this->entity = null;
            $this->entity_id = null;
        } else {
            $this->entity = $entity;
            $this->entity_id = $entityId;
        }

        return $this->entityModelsCache[$cacheKey];
    }

    public static function findPageQueryForApi($blogName, $entity, $entityId)
    {
        return Page::find()->where(['AND', [
            'blog_name' => $blogName,
            'status' => Status::STATUS_ACTIVE,
            'entity' => $entity,
            'entity_id' => $entityId,
        ]]);
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
}
