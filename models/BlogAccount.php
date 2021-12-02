<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "financial_account".
 *
 * @property int $id
 * @property string $name
 * @property string $identity
 * @property string $identity_type
 * @property string $blog_name
 *
 * @property Blog $blogName
 */
class FinancialAccount extends ActiveRecord
{
    const TYPE_CARD = 'card';
    const TYPE_ACCOUNT = 'account';
    const TYPE_SHEBA = 'sheba';

    public static function getTypeList()
    {
        return [
            self::TYPE_CARD => Yii::t('app', 'card number'),
            self::TYPE_ACCOUNT => Yii::t('app', 'account number'),
            self::TYPE_SHEBA => Yii::t('app', 'sheba number'),
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
        return 'financial_account';
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

    public static function getList()
    {
        $list = FinancialAccount::blogValidQuery()->all();
        $list = ArrayHelper::toArray($list);
        return $list;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'name' => $this->name,
            'identity' => $this->identity,
            'identity_type' => $this->identity_type,
        ];
    }

    public static function blogValidQuery($id = null)
    {
        $query = FinancialAccount::find();
        $query->andWhere(['blog_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
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
