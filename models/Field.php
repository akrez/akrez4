<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * This is the model class for table "field".
 *
 * @property int $id
 * @property string $title
 * @property int|null $seq
 * @property int|null $in_summary
 * @property string|null $params
 * @property int|null $category_id
 * @property string|null $user_name
 *
 * @property User $userName
 */
class Field extends ActiveRecord
{

    public $widgets;
    public $unit;
    public $label_no;
    public $label_yes;

    public static function tableName()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['in_summary'], 'boolean'],
            [['params'], 'string'],
            [['seq'], 'integer'],
            [['title'], 'string', 'max' => 64],
            [['title'], 'unique', 'targetAttribute' => ['title', 'category_id', 'user_name'], 'message' => \Yii::t('yii', '{attribute} قبلا ثبت شده است.', ['attribute' => $this->getAttributeLabel('title')])],
            //
            [['unit', 'label_no', 'label_yes'], 'string'],
            [['widgets'], 'each', 'rule' => ['in', 'skipOnError' => true, 'range' => array_keys(FieldList::widgetsList())]],
        ];
    }

    public static function userValidQuery($id = null)
    {
        $query = Field::find();
        $query->andWhere(['user_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'widgets' => [],
            'unit' => null,
            'label_no' => null,
            'label_yes' => null,
        ];

        $this->widgets = $arrayParams['widgets'];
        $this->unit = $arrayParams['unit'];
        $this->label_no = $arrayParams['label_no'];
        $this->label_yes = $arrayParams['label_yes'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->params = [
            'widgets' => $this->widgets,
            'unit' => $this->unit,
            'label_no' => $this->label_no,
            'label_yes' => $this->label_yes,
        ];

        $this->params = Json::encode($this->params);
        return true;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'seq' => $this->seq,
            'in_summary' => $this->in_summary,
            'category_id' => $this->category_id,
            'widgets' => $this->widgets,
            'unit' => $this->unit,
            'label_no' => $this->label_no,
            'label_yes' => $this->label_yes,
        ];
    }

    public static function batchSave($textAreaModel, $categoryModel)
    {
        $correctLines = [];
        $errorLines = [];
        $errors = [];
        foreach ($textAreaModel->explodeLines() as $line) {
            $field = new Field();
            $field->title = $line;
            $field->category_id = $categoryModel->id;
            $field->user_name = Yii::$app->user->getIdentity()->name;
            if ($field->save()) {
                $correctLines[] = $line;
            } else {
                $errorLines[] = $line;
                $errors = array_merge($errors, $field->getErrorSummary(true));
            }
        }
        if ($errors) {
            $textAreaModel->addErrors(['values' => $errors]);
            $textAreaModel->setValues($errorLines);
        }
        return $correctLines;
    }

    /**
     * Gets query for [[Category]].
     *
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Gets query for [[UserName]].
     *
     * @return ActiveQuery
     */
    public function getUserName()
    {
        return $this->hasOne(User::className(), ['name' => 'user_name']);
    }

}
