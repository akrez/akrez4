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

    private $entityModelsCache = [];
    public function setEntity($entity, $entityId)
    {
        $cacheKey = $entity . '-' . $entityId;
        if (!isset($this->entityModelsCache[$cacheKey])) {
            $this->entityModelsCache[$cacheKey] = null;
            //
            if ($entity == self::ENTITY_BLOG) {
                $list = self::entityBlogList();
                if (isset($list[$entityId]) && Yii::$app->user->getIdentity()) {
                    $this->entityModelsCache[$cacheKey] = Yii::$app->user->getIdentity();
                }
            } else if ($entity == self::ENTITY_CATEGORY) {
                $this->entityModelsCache[$cacheKey] = Category::blogValidQuery($entityId)->one();
            } elseif ($entity == self::ENTITY_PRODUCT) {
                $this->entityModelsCache[$cacheKey] = Product::blogValidQuery($entityId)->one();
            }
        }

        if ($this->entityModelsCache[$cacheKey]) {
            $this->entity = $entity;
            $this->entity_id = $entityId;
        } else {
            $this->entity = null;
            $this->entity_id = null;
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
