<?php

namespace app\controllers;

use app\components\Helper;
use app\models\CartSearch;
use app\models\Invoice;
use app\models\InvoiceItemSearch;
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

    public function actionIndex()
    {
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $invoice = Helper::findOrFail(Invoice::blogValidQuery()->andWhere(['id' => $id]));
        //
        $searchModel = new InvoiceItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get(), $invoice);
        return $this->render('view', [
            'invoice' => $invoice,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
