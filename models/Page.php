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

    const PAGE_TYPE_INDEX = 'Index';
    const PAGE_TYPE_CONTACTUS = 'ContactUs';
    const PAGE_TYPE_ABOUTUS = 'AboutUs';

    public static function entityList()
    {
        return [
            self::ENTITY_BLOG => Yii::t('app', 'Blog'),
            self::ENTITY_CATEGORY => Yii::t('app', 'Category'),
            self::ENTITY_PRODUCT => Yii::t('app', 'Product'),
        ];
    }

    public static function entityPage()
    {
        return [
            self::ENTITY_BLOG => [
                self::PAGE_TYPE_INDEX => Yii::t('app', 'Index'),
                self::PAGE_TYPE_CONTACTUS => Yii::t('app', 'Contact Us'),
                self::PAGE_TYPE_ABOUTUS => Yii::t('app', 'About Us'),
            ],
            self::ENTITY_CATEGORY => [
                self::PAGE_TYPE_INDEX => Yii::t('app', 'Index'),
            ],
            self::ENTITY_PRODUCT => [
                self::PAGE_TYPE_INDEX => Yii::t('app', 'Index'),
            ],
        ];
    }

    public static function entityPageList($page = self::ENTITY_BLOG)
    {
        $list = self::entityPage();
        if ($list[$page]) {
            return $list[$page];
        }
        return [];
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
            [['!entity'], 'entityValidation'],
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

    public function entityValidation($attribute, $params, $validator)
    {
        $this->setEntity($this->entity, $this->page_type, $this->entity_id);
    }

    private static $entityModelsCache = [];
    public static function findEntityIdByPage($entity, $pageType, $entityId)
    {
        $cacheKey = $entity . '-' . $pageType . '-' . $entityId;
        if (isset(self::$entityModelsCache[$cacheKey])) {
            return self::$entityModelsCache[$cacheKey];
        }
        $pageList = self::entityPageList($entity);
        if (isset($pageList[$pageType]) && $entityId) {
            if ($entity == self::ENTITY_BLOG) {
                return self::$entityModelsCache[$cacheKey] = Blog::blogValidQuery($entityId)->one();
            } else if ($entity == self::ENTITY_CATEGORY) {
                return self::$entityModelsCache[$cacheKey] = Category::blogValidQuery($entityId)->one();
            } elseif ($entity == self::ENTITY_PRODUCT) {
                return self::$entityModelsCache[$cacheKey] = Product::blogValidQuery($entityId)->one();
            }
        }
        return self::$entityModelsCache[$cacheKey] = null;
    }

    public function setEntity($entity, $pageType, $entityId)
    {
        $entityIdModel = self::findEntityIdByPage($entity, $pageType, $entityId);

        if ($entityIdModel) {
            $this->entity = $entity;
            $this->page_type = $pageType;
            $this->entity_id = $entityId;
        } else {
            $this->entity = null;
            $this->page_type = null;
            $this->entity_id = null;
        }

        return $entityIdModel;
    }

    public static function findPageQueryForApi($blogName, $entity, $page_type, $entityId)
    {
        return Page::find()->where(['AND', [
            'blog_name' => $blogName,
            'status' => Status::STATUS_ACTIVE,
            'entity' => $entity,
            'page_type' => $page_type,
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
