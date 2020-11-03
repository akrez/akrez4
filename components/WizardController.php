<?php

namespace app\components;

use app\models\Status;
use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;

class WizardController extends Component
{
    public $newModel = null;
    public $searchModel = null;
    public $parentModel = null;
    public $parentSearchModel = null;
    //
    public $model = null;
    public $findModel = null;
    public $findParentModel = null;

    public function init()
    {
        parent::init();
        $this->findModel = function ($id) {
            $this->model = $this->searchModel->userValidQuery($id)->one();
            if ($this->model) {
                return $this->model;
            }
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        };
        $this->findParentModel = function ($id) {
            $this->parentModel = $this->parentSearchModel->userValidQuery($id)->one();
            if ($this->parentModel) {
                return $this->parentModel;
            }
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        };
    }

    public function index($queryParams = [], $extraParams = [])
    {
        $dataProvider = null;
        if ($this->searchModel) {
            $dataProvider = $this->searchModel->search($queryParams, $this->parentModel);
        }

        return $extraParams + [
            'newModel' => $this->newModel,
            'searchModel' => $this->searchModel,
            'parentModel' => $this->parentModel,
            'parentSearchModel' => $this->parentSearchModel,
            'model' => $this->model,
            'dataProvider' => $dataProvider,
        ];
    }

    public function create($post, $staticAttributes = [], $setFlash = true)
    {
        if ($this->newModel->load($post)) {
            $this->newModel->setAttributes($staticAttributes, false);
            if ($this->newModel->save()) {
                if ($setFlash) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertAddSuccessfull'));
                }
                return true;
            } else {
                if ($setFlash) {
                    $errors = $this->newModel->getErrorSummary(true);
                    Yii::$app->session->setFlash('danger', reset($errors));
                }
                return false;
            }
        }
        return null;
    }

    public function update($id, $post, $staticAttributes = [], $setFlash = true)
    {
        $this->model = $this->findModel($id);
        if ($this->model->load($post)) {
            $this->model->setAttributes($staticAttributes, false);
            if ($this->model->save()) {
                if ($setFlash) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertUpdateSuccessfull'));
                }
                return true;
            } else {
                if ($setFlash) {
                    $errors = $this->model->getErrorSummary(true);
                    Yii::$app->session->setFlash('danger', reset($errors));
                }
                return false;
            }
        }
        return null;
    }

    public function remove($id, $setFlash = true)
    {
        $this->model = $this->findModel($id);
        $this->model->status = Status::STATUS_DELETED;
        if ($this->model->save(false)) {
            if ($setFlash) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'alertRemoveSuccessfull'));
            }
            return true;
        } else {
            if ($setFlash) {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'alertRemoveUnSuccessfull'));
            }
            return false;
        }
    }

    public function delete($id, $setFlash = true)
    {
        $this->model = $this->findModel($id);
        if ($this->model->delete()) {
            if ($setFlash) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'alertRemoveSuccessfull'));
            }
            return true;
        } else {
            if ($setFlash) {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'alertRemoveUnSuccessfull'));
            }
            return false;
        }
    }

    public function findModel($id)
    {
        return call_user_func($this->findModel, $id);
    }

    public function findParentModel($id)
    {
        return call_user_func($this->findParentModel, $id);
    }
}
