<?php

namespace app\models;

use app\components\Helper;

class Search extends Model
{

    public $field;
    public $operation;
    //
    public $_value;
    //
    public $value;
    public $values;
    public $value_min;
    public $value_max;


    public static $allowedSearchFieldsForApi = [
        'SearchPackage' => ['price'],
        'SearchProduct' => ['title'],
    ];

    public function rules()
    {
        return [
            [['operation', '!_value', '!field',], 'required'],
            [['value', 'values', 'value_min', 'value_max'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        //
        $this->_value = null;
        $this->value = null;
        $this->value_min = null;
        $this->value_max = null;
        $this->values = null;
        if (in_array($this->operation, FieldList::getPluralOperations())) {
            $this->values = (array)$this->values;
            array_map('strval', $this->values);
            if ($this->values) {
                $this->_value = $this->values;
                $this->value = null;
                $this->value_min = null;
                $this->value_max = null;
            }
        } elseif (in_array($this->operation, FieldList::getMinMaxOperations())) {
            if (strlen($this->value_min) > 0 && strlen($this->value_max) > 0) {
                $this->value_min = intval($this->value_min);
                $this->value_max = intval($this->value_max);
                $this->_value = [0 => $this->value_min, 1 => $this->value_max,];
                $this->value = null;
                $this->values = null;
            }
        } else {
            $this->value = strval($this->value);
            if (mb_strlen($this->value)) {
                $this->_value = $this->value;
                $this->value_min = null;
                $this->value_max = null;
                $this->values = null;
            }
        }
        //
        return true;
    }
}
