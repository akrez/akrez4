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
            'code' => 'کد',
            'city' => 'استان',
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
            'entity' => 'نوع',
            'widgets' => 'فیلترها',
            'address' => 'آدرس',
            'package' => 'شرایط فروش',
            'user_id' => 'کاربر',
            'blog_id' => 'کاربر',
            'content' => 'متن',
            'special' => 'ویژه',
            'options' => 'گزینه‌ها',
            'field_id' => 'ویژگی‌',
            'taxonomy' => 'دسته‌بندی',
            'guaranty' => 'گارانتی',
            'language' => 'زبان',

            'label_no' => 'جایگزین عبارت "خیر"',
            'password' => 'رمز عبور',
            'entity_id' => 'شناسه',
            'garanties' => 'گارانتی‌ها',
            'identity' => 'شناسه',
            'label_yes' => 'جایگزین عبارت "بله"',
            'parent_id' => 'مرتبط با',
            'price_min' => 'کمترین قیمت',
            'price_max' => 'بیشترین قیمت',
            'value_max' => 'بیشترین مقدار فیلتر',
            'blog_name' => 'نام کاربری',
            'verify_at' => 'تاریخ ارسال کد فعال‌سازی',
            'color_code' => 'رنگ',
            'in_summary' => 'نمایش در خلاصه',
            'updated_at' => 'تاریخ ویرایش',
            'created_at' => 'تاریخ ایجاد',
            'product_id' => 'محصول',
            'package_id' => 'شرایط فروش',
            'pay_status' => 'وضعیت پرداخت',
            'cache_stock' => 'موجودی انبار',
            'check_stock' => 'بررسی موجودی انبار',
            'postal_code' => 'کد پستی',
            'category_id' => 'دسته‌بندی',
            'reset_token' => 'کد بازیابی رمز عبور',
            'carts_count' => 'تعداد اقلام',
            'max_per_cart' => 'حداکثر مقدار مجاز در سبد خرید',
            'verify_token' => 'کد فعال‌سازی',
            'identity_type' => 'نوع شناسه',
            'captcha' => 'کد تأیید',
            
            'receive_from' => 'تحویل از',
            'telegram_bot_token' => 'Telegram Bot Token',
            
            'receive_until' => 'تحویل تا',
            'receipt' => 'رسید پرداخت',
            'receipt_file' => 'رسید پرداخت',
        ];
    }
}
