<?php

namespace app\models;

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
 * @property string $color
 * @property string $des
 * @property int $product_id
 * @property string|null $blog_name
 *
 * @property Product $product
 * @property Blog $blogName
 */
class Package extends ActiveRecord
{

    public $guaranty;
    public $color;
    public $des;
    //
    public $price_min;
    public $price_max;

    public static function tableName()
    {
        return 'package';
    }

    public function rules()
    {
        return [
            [['status'], 'in', 'range' => array_keys(self::validStatuses())],
            [['color'], 'in', 'range' => array_keys(Color::getList())],
            [['price', 'status', 'guaranty'], 'required'],
            [['price'], 'number'],
            [['guaranty', 'des'], 'safe'],
        ];
    }

    public static function findProductPackageQueryForApi($blogName, $productId)
    {
        return Package::find()->where(['blog_name' => $blogName, 'status' => Status::STATUS_ACTIVE, 'product_id' => $productId]);
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'guaranty' => null,
            'color' => null,
            'des' => null,
        ];
        $this->guaranty = $arrayParams['guaranty'];
        $this->color = $arrayParams['color'];
        $this->des = $arrayParams['des'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'guaranty' => $this->guaranty,
            'color' => $this->color,
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

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'price' => $this->price,
            'product_id' => $this->product_id,
            'blog_name' => $this->blog_name,
            "guaranty" => $this->guaranty,
            "color" => $this->color,
            "des" => $this->des,
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * Gets query for [[BlogName]].
     *
     * @return ActiveQuery
     */
    public function getBlogName()
    {
        return $this->hasOne(Blog::className(), ['name' => 'blog_name']);
    }
}
