<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

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
 * @property string|null $user_name
 *
 * @property Product $product
 * @property User $userName
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

    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }

    public static function userValidQuery($id = null)
    {
        $query = Package::find();
        $query->andWhere(['status' => array_keys(Package::validStatuses())]);
        $query->andWhere(['user_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
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
     * Gets query for [[UserName]].
     *
     * @return ActiveQuery
     */
    public function getUserName()
    {
        return $this->hasOne(User::className(), ['name' => 'user_name']);
    }

}
