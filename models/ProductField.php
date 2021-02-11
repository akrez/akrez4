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
 * @property string $user_name
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

    public static function updateCache($categoryId, $productId = null)
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
                $productField->user_name = $product->user_name;
                if ($productField->validate()) {
                    $datas[$key . $value] = [$key, $value, $product->id, $product->category_id, $product->user_name];
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
            $connection->createCommand()->batchInsert('product_field', ['field', 'value', 'product_id', 'category_id', 'user_name'], $datas)->execute();
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
