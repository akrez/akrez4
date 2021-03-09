<?php

namespace app\models;

use yii\base\Model as BaseModel;

class Model extends BaseModel
{
    public function attributeLabels()
    {
        return static::attributeLabelsList();
    }

    public static function attributeLabelsList()
    {
        return [
            'id' => 'شناسه',
            'seq' => 'مرتبه',
            'des' => 'توضیحات',
            'cnt' => 'تعداد',
            'type' => 'نوع',
            'name' => 'نام',
            'unit' => 'واحد',
            'logo' => 'لوگو',
            'slug' => 'شعار',
            'phone' => 'تلفن',
            'title' => 'عنوان',
            'image' => 'تصویر',
            'width' => 'عرض',
            'value' => 'مقدار',
            'price' => 'قیمت',
            'email' => 'ایمیل',
            'color' => 'رنگ',
            'values' => 'مقادیر',
            'mobile' => 'موبایل',
            'filter' => 'فیلتر',
            'status' => 'وضعیت',
            'height' => 'طول',
            'widget' => 'نمایه',
            'widgets' => 'فیلترها',
            'address' => 'آدرس',
            'package' => 'شرایط فروش',
            'blog_id' => 'کاربر',
            'content' => 'متن',
            'special' => 'ویژه',
            'options' => 'گزینه‌ها',
            'province' => 'استان',
            'field_id' => 'ویژگی‌',
            'taxonomy' => 'دسته‌بندی',
            'guaranty' => 'گارانتی',

            'label_no' => 'جایگزین عبارت "خیر"',
            'password' => 'رمز عبور',
            'garanties' => 'گارانتی‌ها',
            'label_yes' => 'جایگزین عبارت "بله"',
            'parent_id' => 'مرتبط با',
            'price_min' => 'کمترین قیمت',
            'price_max' => 'بیشترین قیمت',
            'value_max' => 'بیشترین مقدار فیلتر',
            'blog_name' => 'نام کاربری',
            'in_summary' => 'نمایش در خلاصه',
            'updated_at' => 'تاریخ ویرایش',
            'created_at' => 'تاریخ ایجاد',
            'product_id' => 'محصول',
            'category_id' => 'دسته‌بندی',
            'reset_token' => 'کد بازیابی رمز عبور',

            'receive_from' => 'تحویل از',
            'receive_until' => 'تحویل تا',
        ];
    }
}
