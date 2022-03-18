<?php

namespace app\controllers;

use app\components\Helper;
use app\models\Invoice;
use app\models\InvoiceSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['index', 'view', 'set-status'],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ],
        ]);
    }

    /**
     * Lists all Invoice models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Invoice model.
     * @param int $id شناسه
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $invoice = Helper::findOrFail(Invoice::blogValidQuery()->andWhere(['id' => $id]));
        return $this->render('view', $invoice->invoiceFullResponse());
    }

    public function actionSetStatus($id, $attribute, $status)
    {
        $invoice = Helper::findOrFail(Invoice::blogValidQuery()->andWhere(['id' => $id]));
        $invoice->setStatus($attribute, $status);
        return $this->redirect(['invoice/view', 'id' => $id]);
    }

    /**
     * Finds the Invoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Invoice::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
