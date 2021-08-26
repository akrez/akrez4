<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "blog_account".
 *
 * @property int $id
 * @property string $name
 * @property string $identity
 * @property string $identity_type
 * @property string $blog_name
 *
 * @property Blog $blogName
 */
class BlogAccount extends ActiveRecord
{
    const TYPE_SHEBA = 'sheba';
    const TYPE_CARD = 'card';
    const TYPE_ACCOUNT = 'account';

    public static function getTypeList()
    {
        return [
            self::TYPE_SHEBA => Yii::t('app', 'sheba'),
            self::TYPE_CARD => Yii::t('app', 'card number'),
            self::TYPE_ACCOUNT => Yii::t('app', 'account number'),
        ];
    }

    public static function getTypeLabel($item)
    {
        $list = self::getTypeList();
        return (isset($list[$item]) ? $list[$item] : null);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'identity', 'identity_type', 'blog_name'], 'required'],
            [['name', 'identity'], 'string', 'max' => 60],
            [['identity_type'], 'string', 'max' => 15],
        ];
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
