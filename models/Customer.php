<?php

namespace app\models;

use app\components\Email;
use Exception;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%Customer}}".
 *
 * @property int $id
 * @property string $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $token
 * @property string|null $password_hash
 * @property string|null $reset_token
 * @property int|null $reset_at
 * @property string|null $email
 * @property string $blog_name
 *
 * @property Blog $blogName
 */
class Customer extends ActiveRecord implements IdentityInterface
{

    const TIMEOUT_RESET = 120;

    public $password;
    public $_customer;

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
            [['email',], 'required', 'on' => 'signup',],
            [['email',], 'unique', 'targetAttribute' => ['email', 'blog_name'], 'message' => Yii::t('yii', '{attribute} "{value}" has already been taken.'), 'on' => 'signup',],
            [['email',], 'email', 'on' => 'signup',],
            [['password',], 'required', 'on' => 'signup',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signup',],
            //signin
            [['!blog_name',], 'required', 'on' => 'signin',],
            [['email',], 'required', 'on' => 'signin',],
            [['email',], 'email', 'on' => 'signin',],
            [['password',], 'required', 'on' => 'signin',],
            [['password',], 'passwordValidation', 'on' => 'signin',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signin',],
            //resetPasswordRequest
            [['!blog_name',], 'required', 'on' => 'resetPasswordRequest',],
            [['email',], 'required', 'on' => 'resetPasswordRequest',],
            [['email',], 'findValidCustomerByEmailValidation', 'on' => 'resetPasswordRequest',],
            [['email',], 'email', 'on' => 'resetPasswordRequest',],
            //resetPassword
            [['!blog_name',], 'required', 'on' => 'resetPassword',],
            [['email',], 'required', 'on' => 'resetPassword',],
            [['email',], 'findValidCustomerByEmailResetTokenValidation', 'on' => 'resetPassword',],
            [['email',], 'email', 'on' => 'resetPassword',],
            [['password',], 'required', 'on' => 'resetPassword',],
            [['password',], 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'resetPassword',],
            [['reset_token',], 'required', 'on' => 'resetPassword',],
        ];
    }

    /////

    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->andWhere(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()->where(['token' => $token])->andWhere(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
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

    public function passwordValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = Customer::findValidCustomerByEmail($this->email, $this->blog_name);
            if ($customer && $customer->validatePassword($this->password)) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function findValidCustomerByEmailValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = Customer::findValidCustomerByEmail($this->email, $this->blog_name);
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function findValidCustomerByEmailResetTokenValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = Customer::findValidCustomerByEmailResetToken($this->email, $this->reset_token, $this->blog_name);
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
        return $this->token = preg_replace("/[^a-z0-9A-Z]+/i", "", Yii::$app->security->generateRandomString());
    }

    public function setResetToken()
    {
        if (empty($this->reset_token) || time() - self::TIMEOUT_RESET > $this->reset_at) {
            $this->reset_token = self::generateResetToken();
        }
        $this->reset_at = time();
    }

    public static function findValidCustomerByEmail($email, $blogName)
    {
        return self::find()
            ->where(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['email' => $email])
            ->one();
    }

    public static function findValidCustomerByEmailResetToken($email, $resetToken, $blogName)
    {
        return self::find()
            ->where(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])
            ->andWhere(['blog_name' => $blogName])
            ->andWhere(['email' => $email])
            ->andWhere(['reset_token' => $resetToken])
            ->andWhere(['>', 'reset_at', time() - self::TIMEOUT_RESET])
            ->one();
    }

    public static function generateResetToken()
    {
        do {
            $rand = rand(100000, 999999);
            $model = self::find()->where(['reset_token' => $rand])->one();
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
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'email' => $this->email,
            'status' => $this->status,
            'blog_name' => $this->blog_name,
            'token' => null,
        ];
    }

    public function toArrayWithToken(array $fields = [], array $expand = [], $recursive = true)
    {
        $array = $this->toArray($fields, $expand, $recursive);
        $array['token'] = $this->token;
        return $array;
    }

    public function response($includeToken = false)
    {
        return [
            'customer' => $includeToken ? $this->toArrayWithToken() : $this->toArray(),
            'errors' => $this->errors,
        ];
    }

    /////

    public static function signup($input, $blogName)
    {
        try {
            $signup = new Customer(['scenario' => 'signup']);
            $signup->load($input, '');
            $signup->status = Status::STATUS_UNVERIFIED;
            $signup->blog_name = $blogName;
            $signup->setAuthKey();
            $signup->setPasswordHash($signup->password);
            $signup->save();
            return $signup;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function signin($input, $blogName)
    {
        try {
            $signin = new Customer(['scenario' => 'signin']);
            $signin->load($input, '');
            $signin->blog_name = $blogName;
            $signin->validate();
            return $signin;
        } catch (Exception $e) {
            return null;
        }
    }

    public function signout()
    {
        try {
            $this->setAuthKey();
            $this->save(false);
            return $this;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function resetPasswordRequest($input, $blogName)
    {
        try {
            $resetPasswordRequest = new Customer(['scenario' => 'resetPasswordRequest']);
            $resetPasswordRequest->load($input, '');
            $resetPasswordRequest->blog_name = $blogName;
            if ($resetPasswordRequest->validate()) {
                $blog = $resetPasswordRequest->getCustomer();
                $blog->setResetToken();
                if ($blog->save(false)) {
                    Email::customerResetPasswordRequest($blog, Yii::$app->user->getIdentity());
                } else {
                    return null;
                }
            }
            return $resetPasswordRequest;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function resetPassword($input, $blogName)
    {
        try {
            $resetPassword = new Customer(['scenario' => 'resetPassword']);
            $resetPassword->load($input, '');
            $resetPassword->blog_name = $blogName;
            if ($resetPassword->validate()) {
                $blog = $resetPassword->getCustomer();
                $blog->reset_token = null;
                $blog->reset_at = null;
                $blog->status = Status::STATUS_ACTIVE;
                $blog->setPasswordHash($resetPassword->password);
                if ($blog->save(false)) {
                    return $resetPassword;
                }
                return null;
            }
            return $resetPassword;
        } catch (Exception $e) {
            return null;
        }
    }
}
