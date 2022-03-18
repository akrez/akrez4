<?php

namespace app\models;

use app\components\Image;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "gallery".
 *
 * @property string $name
 * @property int|null $created_at
 * @property int $width
 * @property int $height
 * @property string $type
 * @property string|null $telegram_id
 * @property int|null $entity_id
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
    const TYPE_RECEIPT = 'receipt';

    public static function typeList()
    {
        return [
            self::TYPE_PRODUCT => Yii::t('app', 'product'),
            self::TYPE_LOGO => Yii::t('app', 'logo'),
            self::TYPE_AVATAR => Yii::t('app', 'avatar'),
            self::TYPE_BROWSER => Yii::t('app', 'browser'),
            self::TYPE_OS => Yii::t('app', 'os'),
            self::TYPE_STORY => Yii::t('app', 'story'),
            self::TYPE_RECEIPT => Yii::t('app', 'receipt'),
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
            [['created_at', 'width', 'height', 'entity_id'], 'integer'],
            [['name'], 'string', 'max' => 16],
            [['type'], 'string', 'max' => 12],
            [['telegram_id'], 'string', 'max' => 127],
            [['blog_name'], 'string', 'max' => 31],
            [['name'], 'unique'],
        ];
    }

    public static function findProductGalleryQueryForApi($blogName, $entityId)
    {
        return Gallery::find()->where(['blog_name' => $blogName, 'type' => Gallery::TYPE_PRODUCT, 'entity_id' => $entityId]);
    }

    public static function findLogoGalleryQueryForApi($blogName)
    {
        return Gallery::find()->where(['blog_name' => $blogName, 'type' => Gallery::TYPE_LOGO]);
    }

    public static function findReceiptGalleryQueryForApi($blogName)
    {
        return Gallery::find()->where(['blog_name' => $blogName, 'type' => Gallery::TYPE_LOGO]);
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

    public function getMyUrl($schema = null)
    {
        return self::getImageUrl($this->type, $this->name, $schema);
    }

    public static function getImageBasePath($type)
    {
        return Yii::getAlias('@webroot/image/') . $type;
    }

    public function updateTelegramId($telegramId)
    {
        $this->telegram_id = $telegramId;
        $this->save();
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

    public static function upload($src, $type, $entityId = null, $options = [], $tryUnlinkSrc = false)
    {
        $gallery = new Gallery();

        $handler = new Image();
        $handler->save($src, self::getImageBasePath($type), $options);
        if ($handler->getError()) {
            $gallery->addErrors(['name' => $handler->getError()]);
        } else {
            $info = $handler->getInfo();
            $gallery->width = $info['desWidth'];
            $gallery->height = $info['desHeight'];
            $gallery->name = $info['desName'];
            $gallery->type = $type;
            $gallery->entity_id = $entityId;
            $gallery->blog_name = \Yii::$app->user->getId();
            $gallery->save();
        }
        if ($tryUnlinkSrc) {
            @unlink($src);
        }
        return $gallery;
    }

    public static function uploadBase64($base64, $type, $entityId = null, $options = [], $tryUnlinkSrc = true)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), $type . '_');
        $base64 = explode(',', $base64) + [1 => '',];
        @file_put_contents($tmpfname, base64_decode($base64[1]));
        return self::upload($tmpfname, $type, $entityId, $options, $tryUnlinkSrc);
    }
}
