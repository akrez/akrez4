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
            [[($this->isNewRecord ? 'code' : '!code')], 'string', 'max' => 31],
            [['code',], 'match', 'pattern' => '/#([A-Fa-f0-9]{6})$/'],
            [['code'], 'unique', 'targetAttribute' => ['code', 'blog_name']],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if (is_string($this->code) && $this->code) {
            $this->code = strtolower($this->code);
        }
        return parent::beforeValidate();
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

    public static function getRawList()
    {
        return [
            '#f08080' => 'بژ تیره',
            '#fa8072' => 'حنایی روشن',
            '#e9967a' => 'قهوه‌ای حنایی',
            '#ffa07a' => 'نارنجی کرم',
            '#ff0000' => 'قرمز',
            '#dc143c' => 'زرشکی',
            '#b22222' => 'شرابی',
            '#8b0000' => 'عنابی تند',
            '#ffc0cb' => 'صورتی',
            '#ffb6c1' => 'صورتی پررنگ',
            '#db7093' => 'شرابی روشن',
            '#ff69b4' => 'سرخابی',
            '#ff1493' => 'شفقی',
            '#c71585' => 'ارغوانی',
            '#ffa500' => 'نارنجی',
            '#ff8c00' => 'نارنجی سیر',
            '#ff7f50' => 'نارنجی پررنگ',
            '#ff6347' => 'قرمز گوجه‌ای',
            '#ff4500' => 'قرمز نارنجی',
            '#ffffe0' => 'شیری',
            '#fffacd' => 'شیرشکری',
            '#fafad2' => 'لیمویی روشن',
            '#ffefd5' => 'هلویی روشن',
            '#ffe4b5' => 'هلویی',
            '#ffdab9' => 'هلویی پررنگ',
            '#eee8aa' => 'نخودی',
            '#f0e68c' => 'خاکی',
            '#ffff00' => 'زرد',
            '#ffd700' => 'کهربایی باز',
            '#bdb76b' => 'ماشی',
            '#adff2f' => 'مغزپسته‌ای',
            '#7fff00' => 'سبز روشن',
            '#7cfc00' => 'مغزپسته‌ای پررنگ',
            '#00ff00' => 'مغزپسته‌ای',
            '#98fb98' => 'سبز کمرنگ',
            '#90ee90' => 'سبز کدر',
            '#00fa9a' => 'یشمی سیر',
            '#00ff7f' => 'یشمی کمرنگ',
            '#9acd32' => 'سبز لجنی',
            '#32cd32' => 'سبز چمنی',
            '#3cb371' => 'خزه‌ای',
            '#2e8b57' => 'خزه‌ای پررنگ',
            '#228b22' => 'شویدی',
            '#008000' => 'سبز',
            '#6b8e23' => 'سبز ارتشی',
            '#808000' => 'زیتونی',
            '#556b2f' => 'زیتونی سیر',
            '#006400' => 'سبز آووکادو',
            '#66cdaa' => 'سبز دریایی',
            '#8fbc8f' => 'سبز دریایی تیره',
            '#20b2aa' => 'سبز کبریتی روشن',
            '#008b8b' => 'سبز کبریتی تیره',
            '#008080' => 'سبز دودی',
            '#00ffff' => 'آبی دریایی',
            '#e0ffff' => 'آبی آسمانی',
            '#afeeee' => 'فیروزه‌ای کدر',
            '#7fffd4' => 'یشمی',
            '#40e0d0' => 'سبز دریایی روشن',
            '#48d1cc' => 'فیروزه‌ای تیره',
            '#00ced1' => 'فیروزه‌ای سیر',
            '#b0e0e6' => 'آبی کبریتی روشن',
            '#b0c4de' => 'بنفش مایل به آبی',
            '#add8e6' => 'آبی کبریتی',
            '#87ceeb' => 'آبی آسمانی سیر',
            '#87cefa' => 'آبی روشن',
            '#00bfff' => 'آبی کمرنگ',
            '#6495ed' => 'آبی کدر',
            '#663399' => 'بنفش ربکا',
            '#4682b4' => 'نیلی متالیک',
            '#5f9ea0' => 'آبی لجنی',
            '#7b68ee' => 'آبی متالیک روشن',
            '#1e90ff' => 'نیلی',
            '#4169e1' => 'فیروزه‌ای فسفری',
            '#0000ff' => 'آبی',
            '#0000cd' => 'آبی سیر',
            '#00008b' => 'سرمه‌ای',
            '#000080' => 'لاجوردی',
            '#191970' => 'آبی نفتی',
            '#e6e6fa' => 'نیلی کمرنگ',
            '#d8bfd8' => 'بادمجانی روشن',
            '#dda0dd' => 'بنفش کدر',
            '#ee82ee' => 'بنفش روشن',
            '#ff00ff' => 'سرخابی روشن',
            '#da70d6' => 'ارکیده',
            '#ba55d3' => 'ارکیده سیر',
            '#9370db' => 'آبی بنفش',
            '#6a5acd' => 'آبی فولادی',
            '#8a2be2' => 'آبی بنفش سیر',
            '#9400d3' => 'بنفش باز',
            '#9932cc' => 'ارکیده بنفش',
            '#8b008b' => 'مخملی',
            '#800080' => 'بنفش',
            '#483d8b' => 'آبی دودی',
            '#4b0082' => 'نیلی سیر',
            '#fff8dc' => 'کاهی',
            '#ffebcd' => 'کاهگلی',
            '#ffe4c4' => 'کرم',
            '#ffdead' => 'کرم سیر',
            '#f5deb3' => 'گندمی',
            '#deb887' => 'خاکی',
            '#d2b48c' => 'برنزه کدر',
            '#bc8f8f' => 'بادمجانی',
            '#f4a460' => 'هلویی سیر',
            '#daa520' => 'خردلی',
            '#b8860b' => 'ماشی سیر',
            '#cd853f' => 'بادامی سیر',
            '#d2691e' => 'عسلی پررنگ',
            '#8b4513' => 'کاکائویی',
            '#a0522d' => 'قهوه‌ای متوسط',
            '#a52a2a' => 'قهوه‌ای',
            '#800000' => 'آلبالویی',
            '#ffffff' => 'سفید',
            '#fffafa' => 'صورتی محو',
            '#f0fff0' => 'یشمی محو',
            '#f5fffa' => 'سفید نعنائی',
            '#f0ffff' => 'آبی محو',
            '#f0f8ff' => 'نیلی محو',
            '#f8f8ff' => 'سفید بنفشه',
            '#f5f5f5' => 'خاکستری محو',
            '#fff5ee' => 'بژ باز',
            '#f5f5dc' => 'هلی',
            '#fdf5e6' => 'بژ روشن',
            '#fffaf0' => 'پوست پیازی',
            '#fffff0' => 'استخوانی',
            '#faebd7' => 'بژ تیره',
            '#faf0e6' => 'کتانی',
            '#fff0f5' => 'صورتی مات',
            '#ffe4e1' => 'بژ',
            '#dcdcdc' => 'خاکستری مات',
            '#d3d3d3' => 'نقره‌ای',
            '#c0c0c0' => 'توسی',
            '#a9a9a9' => 'خاکستری سیر',
            '#808080' => 'خاکستری',
            '#696969' => 'دودی',
            '#778899' => 'سربی',
            '#708090' => 'سربی تیره',
            '#2f4f4f' => 'لجنی تیره',
            '#000000' => 'سیاه',
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
