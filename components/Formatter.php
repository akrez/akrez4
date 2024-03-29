<?php

namespace app\components;

use app\models\Invoice;
use app\models\Status;
use yii\i18n\Formatter as BaseFormatter;

class Formatter extends BaseFormatter
{
    public $datetimefa = 'H:i Y-m-d';

    public function asDatetimefa($value)
    {
        if (!is_numeric($value) && $stt = strtotime($value)) {
            $value = $stt;
        }
        if ($value) {
            return Jdf::jdate($this->datetimefa, $value);
        }
        return $this->nullDisplay;
    }

    public function asStatus($value)
    {
        if (mb_strlen($value)) {
            return Status::getLabel($value);
        }
        return $this->nullDisplay;
    }

    public function asInvoiceStatus($value)
    {
        if (mb_strlen($value)) {
            return Invoice::getLabel($value);
        }
        return $this->nullDisplay;
    }

    public function asPrice($price)
    {
        try {
            if (empty($price) == false) {
                return number_format($price) . ' ریال ';
            }
        } catch (Exception $ex) {
        }
        return $this->nullDisplay;
    }
}
