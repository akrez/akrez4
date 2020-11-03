<?php

namespace app\models;

use app\components\Helper;

class TextArea extends Model
{
    public $values;

    public function rules()
    {
        return [
            [['values'], 'required'],
            [['values'], 'safe'],
        ];
    }

    public function explodeLines()
    {
        $lines = Helper::iexplode("\n", $this->values);
        return Helper::filterArray($lines);
    }

    public function setValues($lines)
    {
        $lines = Helper::filterArray($lines);
        return $this->values = implode("\n", $lines);
    }
}
