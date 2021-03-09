<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
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
 * @property string|null $blog_name
 * @property string|null $unit
 *
 * @property Blog $blogName
 */
class Field extends ActiveRecord
{

    public $widgets;
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
            [['title', 'unit'], 'string', 'max' => 64],
            [['title'], 'unique', 'targetAttribute' => ['title', 'category_id', 'blog_name'], 'message' => \Yii::t('yii', '{attribute} قبلا ثبت شده است.', ['attribute' => $this->getAttributeLabel('title')])],
            //
            [['label_no', 'label_yes'], 'string'],
            [['widgets'], 'each', 'rule' => ['in', 'skipOnError' => true, 'range' => array_keys(FieldList::widgetsList())]],
        ];
    }

    public static function blogValidQuery($id = null)
    {
        $query = Field::find();
        $query->andWhere(['blog_name' => Yii::$app->user->getId(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'widgets' => [],
            'label_no' => null,
            'label_yes' => null,
        ];

        $this->widgets = $arrayParams['widgets'];
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
            $field->blog_name = Yii::$app->user->getIdentity()->name;
            if ($field->save()) {
                $correctLines[] = $line;
            } else {
                $errorLines[] = $line;
                $errors = array_merge($errors, $field->getErrorSummary(true));
            }
        }
        if ($errors) {
            $textAreaModel->addErrors(['values' => $errors]);
        }
        $textAreaModel->setValues($errorLines);
        return $correctLines;
    }

    public static function getFieldsList($categoryId = null)
    {
        $list = [];
        if ($categoryId) {
            $list = Field::find()->where(['category_id' => $categoryId])->orderBy(['seq' => 'DESC'])->indexBy('title')->all();
        }
        return ArrayHelper::toArray($list);
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
     * Gets query for [[BlogName]].
     *
     * @return ActiveQuery
     */
    public function getBlogName()
    {
        return $this->hasOne(Blog::className(), ['name' => 'blog_name']);
    }
}
