<?php

namespace app\models;

class Province extends Model
{

    public static function getList()
    {
        return [
            'tehran' => 'تهران',
            'khuzestan' => 'خوزستان',
            'esfahan' => 'اصفهان',
            'khorasan_razavi' => 'خراسان رضوی',
            'fars' => 'فارس',
            'bushehr' => 'بوشهر',
            'mazandaran' => 'مازندران',
            'alborz' => 'البرز',
            'east_azarbaijan' => 'آذربایجان شرقی',
            'kerman' => 'كرمان',
            'markazi' => 'مرکزی',
            'gilan' => 'گیلان',
            'western_azerbaijan' => 'آذربایجان غربی',
            'hormozgan' => 'هرمزگان',
            'yazd' => 'یزد',
            'qazvin' => 'قزوین',
            'kermanshah' => 'کرمانشاه',
            'hamedan' => 'همدان',
            'sistan_and_baluchistan' => 'سیستان و بلوچستان',
            'golestan' => 'گلستان',
            'kohgiloyeh_boyerahmad' => 'كهگیلویه و بویراحمد',
            'lorestan' => 'لرستان',
            'qom' => 'قم',
            'zanjan' => 'زنجان',
            'ardabil' => 'اردبیل',
            'kurdistan' => 'كردستان',
            'semnan' => 'سمنان',
            'chaharmahal_and_bakhtiari' => 'چهارمحال و بختیاری',
            'ilam' => 'ایلام',
            'north_khorasan' => 'خراسان شمالی',
            'south_khorasan' => 'خراسان جنوبی',
        ];
    }

    public static function getLabel($item)
    {
        $list = self::getList();
        return (isset($list[$item]) ? $list[$item] : null);
    }

}
