<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;

class Helper extends Component
{
    public static function iexplode($delimiters, $string, $limit = PHP_INT_MAX)
    {
        if (!is_array($delimiters)) {
            $delimiters = [$delimiters];
        }
        $del = reset($delimiters);
        //
        $result = [];
        $maskedString = $string;
        while (count($result) + 1 < $limit) {
            $c = 1;
            $maskedStringExploded = str_replace($delimiters, $del, $maskedString, $c);
            $maskedStringExploded = explode($del, $maskedStringExploded, 2);
            if (count($maskedStringExploded) == 2) {
                $result[] = $maskedStringExploded[0];
                $maskedString = $maskedStringExploded[1];
            } else {
                $maskedString = $maskedStringExploded[0];
                break;
            }
        }
        $result[] = $maskedString;
        return $result;
    }

    public static function filterArray($arr, $doFilter = true, $checkUnique = true, $doTrim = true)
    {
        if ($doTrim) {
            $arr = array_map('trim', $arr);
        }
        if ($checkUnique) {
            $arr = array_unique($arr);
        }
        if ($doFilter) {
            $arr = array_filter($arr);
        }
        return $arr;
    }

    public static function templatedArray($template = [], $values = [], $const = [])
    {
        return $const + array_intersect_key($values, $template) + $template;
    }

    public function normalizeEmail($email)
    {
        $email = explode('@', $email);
        $email[0] = str_replace('.', '', $email[0]);
        return implode('@', $email);
    }

    public static function formatDecimal($input, $decimal = 4)
    {
        if (!empty($input) || $input == 0) {
            return number_format((float) $input, $decimal, '.', '');
        }
        return null;
    }

    public static function rulesDumper($scenariosRules, $attributesRules)
    {
        $rules = [];
        foreach ($scenariosRules as $scenario => $scenarioAttributesRules) {
            foreach ($scenarioAttributesRules as $attributeLabel => $scenarioRules) {
                $attribute = ($attributeLabel[0] == '!' ? substr($attributeLabel, 1) : $attributeLabel);
                foreach ($scenarioRules as $scenarioRule) {
                    $rules[] = array_merge([[$attributeLabel]], $scenarioRule, ['on' => $scenario]);
                }
                if (isset($attributesRules[$attribute])) {
                    foreach ($attributesRules[$attribute] as $attributeRule) {
                        $rules[] = array_merge([[$attributeLabel]], $attributeRule, ['on' => $scenario]);
                    }
                }
            }
        }
        return VarDumper::export($rules);
    }

    public static function store(&$newModel, $post, $staticAttributes = [], $setFlash = true)
    {
        if (!$newModel->load($post)) {
            return null;
        }
        //
        $newModel->setAttributes($staticAttributes, false);
        $isSuccessful = $newModel->save();
        //
        if (!$setFlash) {
            return $isSuccessful;
        }
        //
        if ($isSuccessful) {
            if ($newModel->isNewRecord) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'alertAddSuccessfull'));
            } else {
                Yii::$app->session->setFlash('success', Yii::t('app', 'alertUpdateSuccessfull'));
            }
        } else {
            $errors = $newModel->getErrorSummary(true);
            Yii::$app->session->setFlash('danger', reset($errors));
        }
        //
        return $isSuccessful;
    }

    public static function delete(&$model, $setFlash = true)
    {
        $isSuccessful = $model->delete();
        if ($setFlash) {
            if ($isSuccessful) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'alertRemoveSuccessfull'));
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'alertRemoveUnSuccessfull'));
            }
        }
        return $isSuccessful;
    }

    public static function findOrFail(ActiveQuery $query)
    {
        $model = $query->one();
        if ($model) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }
}