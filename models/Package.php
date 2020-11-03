<?php

namespace app\models;

/**
 * This is the model class for table "package".
 *
 * @property int $id
 * @property int $updated_at
 * @property string $status
 * @property double $price
 * @property string $guaranty
 * @property string $color
 * @property string $des
 * @property int $product_id
 *
 * @property Product $product
 */
class Package extends ActiveRecord
{
    public $price_min;
    public $price_max;

    public static function tableName()
    {
        return 'package';
    }

    public function rules()
    {
        return [
            [['status'], 'in', 'range' => Status::getNormalKeys()],
            [['color'], 'in', 'range' => array_keys(Color::getList())],
            [['price', 'status', 'guaranty', '!product_id'], 'required'],
            [['price'], 'number'],
            [['guaranty'], 'string', 'max' => 64],
            [['des'], 'string', 'max' => 1023],
            [['!blog_name'], 'required'],
        ];
    }

    public static function getActivePackagesQueryByCategories($activeCategories)
    {
        $productQuery = Product::find()->select('id')->where(['status' => Status::STATUS_ACTIVE,])->andWhere(['category_id' => array_keys($activeCategories)]);
        return Package::find()->where(['product_id' => $productQuery])->andWhere(['status' => Status::STATUS_ACTIVE]);
    }

}
