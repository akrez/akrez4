<?php

namespace app\models;

use app\components\Helper;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "product_field".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property string $field
 * @property string $value
 * @property int $product_id
 * @property int $category_id
 * @property string $blog_name
 *
 * @property Product $product
 */
class ProductField extends ActiveRecord
{

    public $cnt;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_field';
    }

    public function rules()
    {
        return [
            [['field', 'value'], 'required'],
            [['field',], 'string', 'max' => 64],
            [['value'], 'string', 'max' => 64, 'when' => function ($model) {
                return !is_numeric($model->value);
            }],
            [['value'], 'integer', 'when' => function ($model) {
                return is_numeric($model->value);
            }],
        ];
    }

    public static function batchSave($lines, $product)
    {
        $datas = [];
        //
        $errors = [];
        foreach ($lines as $line) {
            $keyValues = Helper::iexplode([':', ',', '،'], $line);
            $keyValues = Helper::filterArray($keyValues, true, false) + [0 => ''];
            $key = array_shift($keyValues);
            foreach ($keyValues as $value) {
                $productField = new ProductField();
                $productField->field = $key;
                if (is_numeric($value)) {
                    $value = explode('.', $value);
                    $value = $value[0];
                }
                $productField->value = $value;
                $productField->product_id = $product->id;
                $productField->category_id = $product->category_id;
                $productField->blog_name = $product->blog_name;
                if ($productField->validate()) {
                    $datas[$key . $value] = [$key, $value, $product->id, $product->category_id, $product->blog_name];
                } else {
                    $errors = array_merge($errors, $productField->getErrorSummary(true));
                }
            }
        }
        //
        if (!$errors) {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            ProductField::deleteAll(['product_id' => $product->id]);
            $connection->createCommand()->batchInsert('product_field', ['field', 'value', 'product_id', 'category_id', 'blog_name'], $datas)->execute();
            $transaction->commit();
        }
        //
        return $errors;
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
}
