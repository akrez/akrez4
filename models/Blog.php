<?php

namespace app\models;

use app\components\Helper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "blog".
 *
 * @property string $name
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int $status
 * @property string|null $title
 * @property string|null $logo
 * @property string|null $token
 * @property string|null $password_hash
 * @property string|null $verify_token
 * @property int|null $verify_at
 * @property string|null $reset_token
 * @property int|null $reset_at
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $params { "address":"", "phone":"", "instagram":"", "telegram":"", "facebook":"", "twitter":"", "slug":"", "des":"" }
 */
class Blog extends ActiveRecord implements IdentityInterface
{

    const TIMEOUT_RESET = 120;

    public $image;
    public $password;
    public $_blog;
    //
    public $address;
    public $phone;
    public $instagram;
    public $telegram;
    public $facebook;
    public $twitter;
    public $slug;
    public $des;
    //
    public $cache_category;

    public static function tableName()
    {
        return 'blog';
    }

    public function rules()
    {
        return [
            [['title',], 'string', 'max' => 60, 'on' => 'profile',],
            [['des',], 'string', 'on' => 'profile',],
            [['slug',], 'string', 'max' => 160, 'on' => 'profile',],
            [['twitter',], 'match', 'pattern' => '/^[A-Za-z0-9_\.]{1,15}$/', 'on' => 'profile',],
            [['facebook',], 'match', 'pattern' => '/^[A-Za-z0-9_\.]{5,}$/i', 'on' => 'profile',],
            [['telegram',], 'match', 'pattern' => '/^[A-Za-z0-9_\.]+$/i', 'on' => 'profile',],
            [['instagram',], 'match', 'pattern' => '/^[A-Za-z0-9_\.]{5,}$/i', 'on' => 'profile',],
            [['email',], 'email', 'on' => 'profile',],
            [['phone',], 'match', 'pattern' => '/^[0-9+]+$/', 'on' => 'profile',],
            [['address',], 'string', 'max' => 2048, 'on' => 'profile',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'profile',],
            [['image',], 'file', 'on' => 'profile',],
            //
            [['name',], 'required', 'on' => 'signup',],
            [['name',], 'unique', 'on' => 'signup',],
            [['name',], 'match', 'pattern' => '/^[a-z]+$/', 'on' => 'signup',],
            [['title',], 'required', 'on' => 'signup',],
            [['title',], 'string', 'max' => 60, 'on' => 'signup',],
            [['mobile',], 'required', 'on' => 'signup',],
            [['mobile',], 'unique', 'on' => 'signup',],
            [['mobile',], 'match', 'pattern' => '/^[0-9+]+$/', 'on' => 'signup',],
            [['password',], 'required', 'on' => 'signup',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signup',],
            //
            [['name',], 'required', 'on' => 'signin',],
            [['name',], 'match', 'pattern' => '/^[a-z]+$/', 'on' => 'signin',],
            [['password',], 'required', 'on' => 'signin',],
            [['password',], 'signinValidation', 'on' => 'signin',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signin',],
            //
            [['mobile',], 'required', 'on' => 'resetPasswordRequest',],
            [['mobile',], 'resetPasswordRequestValidation', 'on' => 'resetPasswordRequest',],
            [['mobile',], 'match', 'pattern' => '/^[0-9+]+$/', 'on' => 'resetPasswordRequest',],
            //
            [['mobile',], 'required', 'on' => 'verifyRequest',],
            [['mobile',], 'verifyRequestValidation', 'on' => 'verifyRequest',],
            [['mobile',], 'match', 'pattern' => '/^[0-9+]+$/', 'on' => 'verifyRequest',],
            //
            [['mobile',], 'required', 'on' => 'resetPassword',],
            [['mobile',], 'resetPasswordValidation', 'on' => 'resetPassword',],
            [['mobile',], 'match', 'pattern' => '/^[0-9+]+$/', 'on' => 'resetPassword',],
            [['password',], 'required', 'on' => 'resetPassword',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'resetPassword',],
            [['reset_token',], 'required', 'on' => 'resetPassword',],
            //
            [['mobile',], 'required', 'on' => 'verify',],
            [['mobile',], 'verifyValidation', 'on' => 'verify',],
            [['mobile',], 'match', 'pattern' => '/^[0-9+]+$/', 'on' => 'verify',],
            [['verify_token',], 'required', 'on' => 'verify',],
        ];
    }

    public function dumpRules()
    {
        $attributesRules = [
            'name' => [
                ['match', 'pattern' => '/^[a-z]+$/'],
            ],
            'title' => [
                ['string', 'max' => 60],
            ],
            'email' => [
                ['email'],
            ],
            //
            'password' => [
                ['minLenValidation', 'params' => ['min' => 6]],
            ],
            //
            'des' => [
                ['string'],
            ],
            'slug' => [
                ['string', 'max' => 160],
            ],
            'twitter' => [
                ['match', 'pattern' => '/^[A-Za-z0-9_\.]{1,15}$/'],
            ],
            'facebook' => [
                ['match', 'pattern' => '/^[A-Za-z0-9_\.]{5,}$/i'],
            ],
            'telegram' => [
                ['match', 'pattern' => '/^[A-Za-z0-9_\.]+$/i'],
            ],
            'instagram' => [
                ['match', 'pattern' => '/^[A-Za-z0-9_\.]{5,}$/i'],
            ],
            'mobile' => [
                ['match', 'pattern' => '/^[0-9+]+$/'],
            ],
            'phone' => [
                ['match', 'pattern' => '/^[0-9+]+$/'],
            ],
            'address' => [
                ['string', 'max' => 2048],
            ],
            //
            'image' => [
                ['file'],
            ],
        ];

        $scenariosRules = [
            'profile' => [
                'title' => [],
                'des' => [],
                'slug' => [],
                'twitter' => [],
                'facebook' => [],
                'telegram' => [],
                'instagram' => [],
                'email' => [],
                'phone' => [],
                'address' => [],
                'password' => [],
                'image' => [],
            ],
            'signup' => [
                'name' => [['required'], ['unique'],],
                'title' => [['required'],],
                'mobile' => [['required'], ['unique'],],
                'password' => [['required'],],
            ],
            'signin' => [
                'name' => [['required']],
                'password' => [['required'], ['signinValidation']],
            ],
            'resetPasswordRequest' => [
                'mobile' => [['required'], ['resetPasswordRequestValidation']],
            ],
            'verifyRequest' => [
                'mobile' => [['required'], ['verifyRequestValidation']],
            ],
            'resetPassword' => [
                'mobile' => [['required'], ['resetPasswordValidation']],
                'password' => [['required']],
                'reset_token' => [['required']],
            ],
            'verify' => [
                'mobile' => [['required'], ['verifyValidation']],
                'verify_token' => [['required']],
            ],
        ];

        die(str_replace(["0 => [", "1 => '"], ["[", "'"], Helper::rulesDumper($scenariosRules, $attributesRules)));
    }

    /////
    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'des' => null,
            'slug' => null,
            'twitter' => null,
            'facebook' => null,
            'telegram' => null,
            'instagram' => null,
            'phone' => null,
            'address' => null,
            //
            'cache_category' => [],
        ];
        $this->des = $arrayParams['des'];
        $this->slug = $arrayParams['slug'];
        $this->instagram = $arrayParams['instagram'];
        $this->telegram = $arrayParams['telegram'];
        $this->facebook = $arrayParams['facebook'];
        $this->twitter = $arrayParams['twitter'];
        $this->phone = $arrayParams['phone'];
        $this->address = $arrayParams['address'];
        //
        $this->cache_category = $arrayParams['cache_category'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'des' => $this->des,
            'slug' => $this->slug,
            'instagram' => $this->instagram,
            'telegram' => $this->telegram,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'phone' => $this->phone,
            'address' => $this->address,
            //
            'cache_category' => (array) $this->cache_category,
        ];
        $this->params = Json::encode($this->params);
        return true;
    }

    /////

    public function signinValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $blog = self::find()->where(['status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->andWhere(['name' => $this->name])->one();
            if ($blog && $blog->validatePassword($this->password)) {
                return $this->_blog = $blog;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_blog = null;
    }

    public function resetPasswordRequestValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $blog = self::find()->where(['status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->andWhere(['mobile' => $this->mobile])->one();
            if ($blog) {
                return $this->_blog = $blog;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_blog = null;
    }

    public function verifyRequestValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $blog = self::find()->where(['status' => [Status::STATUS_UNVERIFIED]])->andWhere(['mobile' => $this->mobile])->one();
            if ($blog) {
                return $this->_blog = $blog;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_blog = null;
    }

    public function resetPasswordValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $blog = self::find()->where(['status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->andWhere(['mobile' => $this->mobile])->andWhere(['reset_token' => $this->reset_token])->andWhere(['>', 'reset_at', time() - self::TIMEOUT_RESET])->one();
            if ($blog) {
                return $this->_blog = $blog;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_blog = null;
    }

    public function verifyValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $blog = self::find()->where(['status' => [Status::STATUS_UNVERIFIED]])->andWhere(['mobile' => $this->mobile])->andWhere(['verify_token' => $this->verify_token])->andWhere(['>', 'verify_at', time() - self::TIMEOUT_RESET])->one();
            if ($blog) {
                return $this->_blog = $blog;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_blog = null;
    }

    ////

    public static function findIdentity($name)
    {
        return static::find()->where(['name' => $name])->andWhere(['status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()->where(['token' => $token])->andWhere(['status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->token;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /////

    public function minLenValidation($attribute, $params, $validator)
    {
        $min = $params['min'];
        if (strlen($this->$attribute) < $min) {
            $this->addError($attribute, Yii::t('yii', '{attribute} must be no less than {min}.', ['min' => $min, 'attribute' => $this->getAttributeLabel($attribute)]));
        }
    }

    public function maxLenValidation($attribute, $params, $validator)
    {
        $max = $params['max'];
        if ($max < strlen($this->$attribute)) {
            $this->addError($attribute, Yii::t('yii', '{attribute} must be no greater than {max}.', ['max' => $max, 'attribute' => $this->getAttributeLabel($attribute)]));
        }
    }

    public function setPasswordHash($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function setAuthKey()
    {
        return $this->token = Yii::$app->security->generateRandomString();
    }

    public function setVerifyToken($setNull = false)
    {
        if ($setNull === true) {
            $this->verify_token = null;
            $this->verify_at = null;
        } else {
            if (empty($this->verify_token) || time() - self::TIMEOUT_RESET > $this->verify_at) {
                $this->verify_token = self::generateVerifyToken();
            }
            $this->verify_at = time();
        }
    }

    public function setResetToken($setNull = false)
    {
        if ($setNull === true) {
            $this->reset_token = null;
            $this->reset_at = null;
        } else {
            if (empty($this->reset_token) || time() - self::TIMEOUT_RESET > $this->reset_at) {
                $this->reset_token = self::generateResetToken();
            }
            $this->reset_at = time();
        }
    }

    public function generateVerifyToken()
    {
        do {
            $rand = rand(10000, 99999);
            $model = self::find()->where(['verify_token' => $rand])->one();
        } while ($model != null);
        return $rand;
    }

    public function generateResetToken()
    {
        do {
            $rand = rand(10000, 99999);
            $model = self::find()->where(['reset_token' => $rand])->one();
        } while ($model != null);
        return $rand;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getBlog()
    {
        return $this->_blog;
    }

    public static function findBlogForApi($name)
    {
        return static::find()->where(['name' => $name, 'status' => Status::STATUS_ACTIVE])->one();
    }

    public static function print($attribute)
    {
        $blog = Yii::$app->user->getIdentity();
        if ($blog) {
            return Html::encode($blog->$attribute);
        }
        return null;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'created_at' => $this->created_at,
            'name' => $this->name,
            'title' => $this->title,
            'slug' => $this->slug,
            'des' => $this->des,
            'logo' => $this->logo,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'instagram' => $this->instagram,
            'telegram' => $this->telegram,
            'address' => $this->address,
            'twitter' => $this->twitter,
        ];
    }
}
