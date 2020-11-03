<?php

namespace app\models;

use Yii;

class Status extends Model
{
    const STATUS_UNVERIFIED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 2;
    const STATUS_BLOCKED = 3;
    const STATUS_DELETED = 4;

    public static function getList()
    {
        return [
            self::STATUS_UNVERIFIED => Yii::t('app', 'Unverified'),
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_DISABLE => Yii::t('app', 'Disable'),
            self::STATUS_BLOCKED => Yii::t('app', 'Blocked'),
            self::STATUS_DELETED => Yii::t('app', 'Deleted'),
        ];
    }

    public static function getLabel($item)
    {
        switch ($item) {
            case self::STATUS_UNVERIFIED:
                return Yii::t('app', 'Unverified');
            case self::STATUS_ACTIVE:
                return Yii::t('app', 'Active');
            case self::STATUS_DISABLE:
                return Yii::t('app', 'Disable');
            case self::STATUS_BLOCKED:
                return Yii::t('app', 'Blocked');
            case self::STATUS_DELETED:
                return Yii::t('app', 'Deleted');
        }
        return null;
    }
}
