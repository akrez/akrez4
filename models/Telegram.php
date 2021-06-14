<?php

namespace app\models;

/**
 * This is the model class for table "telegram".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string|null $update_id
 * @property string|null $message
 * @property string|null $blog_name
 */
class Telegram extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telegram';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['update_id'], 'string', 'max' => 15],
            [['blog_name'], 'string', 'max' => 31],
        ];
    }
}
