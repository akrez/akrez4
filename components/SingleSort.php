<?php

namespace app\components;

use yii\base\Component;

class SingleSort extends Component
{

    public $sort;
    //
    public $defaultSort;
    public $sortAttributes;
    //
    public $order;
    public $attribute;

    public function init()
    {
        parent::init();

        if (empty($this->sort)) {
            $this->sort = $this->defaultSort;
        }

        if (empty($this->sort) || !isset($this->sortAttributes[$this->sort])) {
            $this->sort = key($this->sortAttributes);
        }

        if ($this->sort[0] == '-') {
            $this->order = SORT_DESC;
            $this->attribute = substr($this->sort, 1);
        } else {
            $this->order = SORT_ASC;
            $this->attribute = $this->sort;
        }
    }

}
