<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "telegram_contenttransition".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string|null $forward_from
 * @property string|null $update_id
 * @property string|null $message
 */
class TelegramContenttransition extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telegram_contenttransition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['forward_from'], 'string', 'max' => 31],
            [['update_id'], 'string', 'max' => 15],
        ];
    }

    public static function singleSave($message)
    {
        $model = new TelegramContenttransition();
        $model->message = $message;
        $model->save();
    }
}
