<?php

namespace app\models;

use app\components\Cache;
use app\components\Helper;
use Throwable;
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

    const TIMEOUT_RESET = 300;
    const SIGNUP_STATUS = Status::STATUS_UNVERIFIED;

    public $password;
    public $_customer;
    //
    public $cache_addresses; #TODO

    public static function tableName()
    {
        return '{{%customer}}';
    }

    public function rules()
    {
        return [
            //signup
            [['!blog_name',], 'required', 'on' => 'signup',],
            [['password',], 'required', 'on' => 'signup',],
            [['password',], 'string', 'min' => 6, 'strict' => false, 'on' => 'signup',],
            [['mobile',], 'required', 'on' => 'signup',],
            [['mobile',], 'match', 'pattern' => '/^09[0-9]{9}$/', 'on' => 'signup',],
            [['mobile',], 'signupValidation', 'on' => 'signup',],
            //login
            [['!blog_name',], 'required', 'on' => 'login',],
            [['!status',], 'required', 'on' => 'login',],
            [['!status',], 'in', 'range' => self::validStatusesKey(), 'on' => 'login',],
            [['password',], 'required', 'on' => 'login',],
            [['password',], 'string', 'min' => 6, 'strict' => false, 'on' => 'login',],
            [['password',], 'loginValidation', 'on' => 'login',],
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
            [['password',], 'string', 'min' => 6, 'strict' => false, 'on' => 'resetPassword',],
            [['reset_token',], 'resetPasswordValidation', 'on' => 'resetPassword',],
            [['reset_token',], 'required', 'on' => 'resetPassword',],
            //verify
            [['!blog_name',], 'required', 'on' => 'resetPassword',],
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

    public function signupValidation($attribute, $params)
    {
        $customer = null;
        if (!$this->hasErrors()) {
            $customer = self::findCustomerQueryForApi($this->blog_name, $this->mobile, [])
                ->one();
            if ($customer) {
                $message = Yii::t('yii', '{attribute} "{value}" has already been taken.', [
                    'attribute' => $this->getAttributeLabel($attribute),
                    'value' => $this->$attribute,
                ]);
                $this->addError($attribute, $message);
            }
        }
        return $this->_customer = $customer;
    }

    public function loginValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $passwordIsValid = Yii::$app->security->validatePassword($this->$attribute, $this->password_hash);
            if (!$passwordIsValid) {
                $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
            }
        }
    }

    public function resetPasswordRequestValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = self::findCustomerQueryForApi($this->blog_name, $this->mobile, self::validStatusesKey())
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
            $customer = self::findCustomerQueryForApi($this->blog_name, $this->mobile, self::SIGNUP_STATUS)
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
            $customer = self::findCustomerQueryForApi($this->blog_name, $this->mobile, self::validStatusesKey())
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
            $customer = self::findCustomerQueryForApi($this->blog_name, $this->mobile, self::SIGNUP_STATUS)
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

    /////

    public static function signup($blogName, $data)
    {
        try {
            $signup = new Customer(['scenario' => 'signup']);
            $signup->load($data, '');
            $signup->blog_name = $blogName;
            $signup->status = self::SIGNUP_STATUS;
            $signup->setAuthKey();
            $signup->setPasswordHash($signup->password);
            $signup->save();
            return $signup;
        } catch (Throwable $e) {
        }
        return null;
    }

    public function setPasswordHash($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function setAuthKey()
    {
        return $this->token = Yii::$app->security->generateRandomString();
    }

    public function setAttributeToken($attribute, $attributeAt, $setNull = false)
    {
        if ($setNull === true) {
            $this->$attribute = null;
            $this->$attributeAt = null;
        } else {
            if (empty($this->$attribute) || time() - self::TIMEOUT_RESET > $this->$attributeAt) {
                do {
                    $this->$attribute = mt_rand(100000, 999999);
                    $model = self::find()->where([$attribute => $this->$attribute])->one();
                } while ($model != null);
            }
            $this->$attributeAt = time();
        }
    }

    /**
     * @return Customer
     **/
    public function getCustomer()
    {
        return $this->_customer;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true, $includeToken = false)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'mobile' => $this->mobile,
            'status' => $this->status,
            'token' => ($includeToken ? $this->token : null),
        ];
    }

    public function response($action = null, $status = null, $includeToken = false)
    {
        $response = [
            'customer' => $this->toArray([], [], true, $includeToken),
            'errors' => $this->errors,
        ];
        if ($action !== null) {
            $response['action'] = $action;
        }
        if ($status !== null) {
            $response['status'] = $status;
        }
        return $response;
    }

    public static function validStatuses($includeUnverifiedStatus = false)
    {
        $statuses = [
            Status::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Status::STATUS_DISABLE => Yii::t('app', 'Disable'),
        ];
        if ($includeUnverifiedStatus) {
            $statuses[Status::STATUS_UNVERIFIED] = Yii::t('app', 'Unverified');
        }
        return $statuses;
    }

    public static function validStatusesKey($includeUnverifiedStatus = false)
    {
        return array_keys(self::validStatuses($includeUnverifiedStatus = false));
    }

    public static function blogValidQuery($id = null, $includeUnverifiedStatus = true)
    {
        $query = Customer::find();
        $query->andWhere(['status' => array_keys(Customer::validStatuses($includeUnverifiedStatus))]);
        $query->andWhere(['blog_name' => Yii::$app->user->getId()]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public static function findCustomerQueryForApi($blogName, $mobile, $statusMode)
    {
        return Customer::find()
            ->where(['blog_name' =>  $blogName])
            ->andWhere(['mobile' => $mobile])
            ->andFilterWhere(['status' => $statusMode]);
    }

    public static function findCustomerQueryForApiById($blogName, $id, $statusMode)
    {
        return Customer::find()
            ->where(['blog_name' =>  $blogName])
            ->andWhere(['id' => $id])
            ->andFilterWhere(['status' => $statusMode]);
    }
}
