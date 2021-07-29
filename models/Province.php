<?php

namespace app\models;

use Yii;

class Province extends Model
{

    public static function getList()
    {
        return [
            'IR-01' => Yii::t("app", "Azarbayjan-e Sharqi"),
            'IR-02' => Yii::t("app", "Azarbayjan-e Gharbi"),
            'IR-03' => Yii::t("app", "Ardabil"),
            'IR-04' => Yii::t("app", "Esfahan"),
            'IR-05' => Yii::t("app", "Ilam"),
            'IR-06' => Yii::t("app", "Bushehr"),
            'IR-07' => Yii::t("app", "Tehran"),
            'IR-08' => Yii::t("app", "Chahar Maal va Bakhtiari"),
            'IR-10' => Yii::t("app", "Khuzestan"),
            'IR-11' => Yii::t("app", "Zanjan"),
            'IR-12' => Yii::t("app", "Semnan"),
            'IR-13' => Yii::t("app", "Sistan va Baluchestan"),
            'IR-14' => Yii::t("app", "Fars"),
            'IR-15' => Yii::t("app", "Kerman"),
            'IR-16' => Yii::t("app", "Kordestan"),
            'IR-17' => Yii::t("app", "Kermanshah"),
            'IR-18' => Yii::t("app", "Kohgiluyeh va Bowyer Amad"),
            'IR-19' => Yii::t("app", "Gilan"),
            'IR-20' => Yii::t("app", "Lorestan"),
            'IR-21' => Yii::t("app", "Mazandaran"),
            'IR-22' => Yii::t("app", "Markazi"),
            'IR-23' => Yii::t("app", "Hormozgan"),
            'IR-24' => Yii::t("app", "Hamadan"),
            'IR-25' => Yii::t("app", "Yazd"),
            'IR-26' => Yii::t("app", "Qom"),
            'IR-27' => Yii::t("app", "Golestan"),
            'IR-28' => Yii::t("app", "Qazvin"),
            'IR-29' => Yii::t("app", "Khorasan-e Jonubi"),
            'IR-30' => Yii::t("app", "Khorasan-e Razavi"),
            'IR-31' => Yii::t("app", "Khorasan-e Shomali"),
            'IR-32' => Yii::t("app", "Alborz"),
        ];
    }

    public static function getLabel($item)
    {
        $list = self::getList();
        return (isset($list[$item]) ? $list[$item] : null);
    }
}
