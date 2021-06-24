<?php

namespace app\models;

use app\components\Cache;
use Yii;

/**
 * This is the model class for table "color".
 *
 * @property int $id
 * @property string $title
 * @property string $code
 * @property string|null $blog_name
 *
 * @property Blog $blogName
 */
class Color extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'color';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'code'], 'required'],
            [['title'], 'string', 'max' => 31],
            [['code'], 'string', 'max' => 12],
            [['code',], 'match', 'pattern' => '/#([a-f0-9]{3}){1,2}\b/i'],
        ];
    }

    public static function blogValidQuery($id = null)
    {
        $query = Color::find();
        $query->andWhere(['blog_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public static function getLabel($item)
    {
        $list = self::getList();
        return (isset($list[$item]) ? $list[$item] : null);
    }

    public static function getList()
    {
        return Cache::getBlogCacheColor(Yii::$app->user->getIdentity());
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
