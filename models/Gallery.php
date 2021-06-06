<?php

namespace app\models;

use app\components\Image;
use Yii;
use yii\helpers\Url;

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
    const TYPE_BROWSER = 'browser';
    const TYPE_OS = 'os';
    const TYPE_STORY = 'story';

    public static function typeList()
    {
        return [
            self::TYPE_PRODUCT => Yii::t('app', 'product'),
            self::TYPE_LOGO => Yii::t('app', 'logo'),
            self::TYPE_AVATAR => Yii::t('app', 'avatar'),
            self::TYPE_BROWSER => Yii::t('app', 'browser'),
            self::TYPE_OS => Yii::t('app', 'os'),
            self::TYPE_STORY => Yii::t('app', 'story'),
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
        return $this->hasMany(Blog::class, ['logo' => 'name']);
    }

    private static function getUrl($type, $name, $schema = null)
    {
        $dir = ($type === null ? '' : '/' . $type);
        return Url::to(Yii::getAlias('@web/image') . $dir . '/' . $name, $schema);
    }

    public static function getImageUrl($type, $name, $schema = null)
    {
        if ($type == self::TYPE_OS) {
            if (in_array($name, ['Android', 'Chrome OS', 'iOS', 'Linux', 'Ubuntu', 'Windows'])) {
                return self::getUrl(self::TYPE_OS, $name . '.svg', $schema);
            }
            return null;
        } elseif ($type == self::TYPE_BROWSER) {
            if (in_array($name, ['Chrome', 'Edge', 'Firefox', 'Safari', 'Samsung Internet', 'Opera', 'Internet Explorer',])) {
                return self::getUrl(self::TYPE_BROWSER, $name . '.svg', $schema);
            }
            return null;
        } elseif ($type == self::TYPE_STORY) {
            return self::getUrl(self::TYPE_STORY, $name . '.svg', $schema);
        }
        return self::getUrl($type, $name, $schema);
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
