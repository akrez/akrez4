<?php

namespace app\models;

use app\components\Cache;
use app\components\Helper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property int $status
 * @property string|null $token
 * @property string|null $password_hash
 * @property string|null $verify_token
 * @property int|null $verify_at
 * @property string|null $reset_token
 * @property int|null $reset_at
 * @property string|null $mobile
 * @property string|null $name
 * @property string $blog_name
 * @property string|null $params
 *
 * @property Blog $blogName
 */
class Customer extends ActiveRecord implements IdentityInterface
{

    const TIMEOUT_RESET = 120;

    public $password;
    public $_customer;
    //
    public $cache_addresses; #TODO

    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //signup
            [['!blog_name',], 'required', 'on' => 'signup',],
            [['mobile',], 'required', 'on' => 'signup',],
            [['mobile',], 'unique', 'targetAttribute' => ['mobile', 'blog_name'], 'message' => Yii::t('yii', '{attribute} "{value}" has already been taken.'), 'on' => 'signup',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'signup',],
            [['password',], 'required', 'on' => 'signup',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signup',],
            //signin
            [['!blog_name',], 'required', 'on' => 'signin',],
            [['mobile',], 'required', 'on' => 'signin',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'signin',],
            [['password',], 'required', 'on' => 'signin',],
            [['password',], 'signinValidation', 'on' => 'signin',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signin',],
            //resetPasswordRequest
            [['!blog_name',], 'required', 'on' => 'resetPasswordRequest',],
            [['mobile',], 'required', 'on' => 'resetPasswordRequest',],
            [['mobile',], 'resetPasswordRequestValidation', 'on' => 'resetPasswordRequest',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'resetPasswordRequest',],
            //resetPassword
            [['!blog_name',], 'required', 'on' => 'resetPassword',],
            [['mobile',], 'required', 'on' => 'resetPassword',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'resetPassword',],
            [['password',], 'required', 'on' => 'resetPassword',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'resetPassword',],
            [['reset_token',], 'resetPasswordValidation', 'on' => 'resetPassword',],
            [['reset_token',], 'required', 'on' => 'resetPassword',],
            //verify
            [['mobile',], 'required', 'on' => 'verify',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'verify',],
            [['verify_token',], 'verifyValidation', 'on' => 'verify',],
            [['verify_token',], 'required', 'on' => 'verify',],
            //verifyRequest
            [['!blog_name',], 'required', 'on' => 'verifyRequest',],
            [['mobile',], 'required', 'on' => 'verifyRequest',],
            [['mobile',], 'verifyRequestValidation', 'on' => 'verifyRequest',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'verifyRequest',],
        ];
    }

    /////

    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->andWhere(['status' => array_keys(Customer::validStatuses())])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()->where(['token' => $token])->andWhere(['status' => array_keys(Customer::validStatuses())])->one();
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

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'cache_addresses' => [],
        ];
        $this->cache_addresses = $arrayParams['cache_addresses'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'cache_addresses' => (array) $this->cache_addresses,
        ];
        $this->params = Json::encode($this->params);
        return true;
    }

    /////

    public function signinValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = self::blogValidQuery($this->blog_name, $this->mobile, false)
                ->one();
            if ($customer && $customer->validatePassword($this->password)) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function resetPasswordRequestValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = self::blogValidQuery($this->blog_name, $this->mobile, false)
                ->one();
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function verifyRequestValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = self::blogValidQuery($this->blog_name, $this->mobile)
                ->one();
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function resetPasswordValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = self::blogValidQuery($this->blog_name, $this->mobile, false)
                ->andWhere(['reset_token' => $this->reset_token])
                ->andWhere(['>', 'reset_at', time() - self::TIMEOUT_RESET])
                ->one();
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function verifyValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = self::blogValidQuery($this->blog_name, $this->mobile)
                ->andWhere(['verify_token' => $this->verify_token])
                ->andWhere(['>', 'verify_at', time() - self::TIMEOUT_RESET])
                ->one();
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

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
                $this->verify_token = self::generateToken('verify_token');
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
                $this->reset_token = self::generateToken('reset_token');
            }
            $this->reset_at = time();
        }
    }

    public static function generateToken($attribute)
    {
        do {
            $rand = mt_rand(100000, 999999);
            $model = self::find()->where([$attribute => $rand])->one();
        } while ($model != null);
        return $rand;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'mobile' => $this->mobile,
        ];
    }
    public static function validStatuses()
    {
        return [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
    }

    public static function blogValidQuery($blogName, $mobile, $justUnverifiedStatus = true)
    {
        $statuses = ($justUnverifiedStatus ? Status::STATUS_UNVERIFIED : array_keys(self::validStatuses()));
        return Customer::find()
            ->where(['blog_name' =>  $blogName])
            ->andWhere(['mobile' => $mobile])
            ->andWhere(['status' => $statuses]);
    }
}
