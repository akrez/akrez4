<?php

namespace app\models;

use app\components\Image;
use Yii;

/**
 * This is the model class for table "gallery".
 *
 * @property string $name
 * @property int|null $updated_at
 * @property int $width
 * @property int $height
 * @property string $type
 * @property int|null $product_id
 * @property string|null $blog_name
 *
 * @property Blog[] $blogs
 */
class Gallery extends ActiveRecord
{
    public $image;

    const TYPE_PRODUCT = 'product';
    const TYPE_LOGO = 'logo';
    const TYPE_AVATAR = 'avatar';

    public static function typeList()
    {
        return [
            self::TYPE_PRODUCT => Yii::t('app', 'product'),
            self::TYPE_LOGO => Yii::t('app', 'logo'),
            self::TYPE_AVATAR => Yii::t('app', 'avatar'),
        ];
    }

    public static function tableName()
    {
        return 'gallery';
    }

    public function rules()
    {
        return [
            [['name', 'width', 'height', 'type'], 'required'],
            [['updated_at', 'width', 'height', 'product_id'], 'integer'],
            [['name'], 'string', 'max' => 16],
            [['type'], 'string', 'max' => 12],
            [['blog_name'], 'string', 'max' => 31],
            [['name'], 'unique'],
        ];
    }

    public static function findProductGalleryQueryForApi($blogName, $productId)
    {
        return Gallery::find()->where(['blog_name' => $blogName, 'type' => Gallery::TYPE_PRODUCT, 'product_id' => $productId]);
    }

    public function getBlogs()
    {
        return $this->hasMany(Blog::className(), ['logo' => 'name']);
    }

    public static function getImageUrl($type, $name)
    {
        return Yii::getAlias('@web/image/') . $type . '/' . $name;
    }

    public static function getImageBasePath($type)
    {
        return Yii::getAlias('@webroot/image/') . $type;
    }

    public static function getImagePath($type, $name)
    {
        return Yii::getAlias('@webroot/image/') . $type . '/' . $name;
    }

    public function delete()
    {
        $path = self::getImagePath($this->type, $this->name);
        @unlink($path);
        return parent::delete();
    }

    public static function upload($src, $type, $productId = null)
    {
        $gallery = new Gallery();

        $handler = new Image();
        $handler->save($src, self::getImageBasePath($type));
        if ($handler->getError()) {
            $gallery->addErrors(['name' => $handler->getError()]);
        } else {
            $info = $handler->getInfo();
            $gallery->width = $info['desWidth'];
            $gallery->height = $info['desHeight'];
            $gallery->name = $info['desName'];
            $gallery->type = $type;
            $gallery->product_id = $productId;
            $gallery->blog_name = \Yii::$app->user->getId();
            $gallery->save();
        }
        return $gallery;
    }
}
