<?php

namespace app\models;

use app\components\Helper;

class Search extends Model
{

    public $widget;
    public $field;
    public $operation;
    //
    public $_value;
    //
    public $value;
    public $values;
    public $value_min;
    public $value_max;

    public function rules()
    {
        return [
            [['operation', '!_value', '!field', 'widget'], 'required'],
            [['value', 'values', 'value_min', 'value_max'], 'safe'],
            [['widget'], 'in', 'range' => array_keys(FieldList::widgetsList())],
        ];
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'widget' => $this->widget,
            'field' => $this->field,
            'operation' => $this->operation,
            'value' => $this->value,
            'values' => $this->values,
            'value_min' => $this->value_min,
            'value_max' => $this->value_max,
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        //
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
