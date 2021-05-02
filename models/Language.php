<?php

namespace app\models;

use Yii;

class Language extends Model
{
    const LANGUAGE_FA = 'fa-IR';
    const LANGUAGE_EN = 'en-US';

    public static function getList()
    {
        return [
            self::LANGUAGE_FA => 'فارسی',
            self::LANGUAGE_EN => 'English',
        ];
    }

    public static function getLabel($item)
    {
        $list = self::getList();
        return (isset($list[$item]) ? $list[$item] : null);
    }
}
