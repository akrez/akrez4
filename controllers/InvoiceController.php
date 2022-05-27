<?php

namespace app\controllers;

use app\components\Helper;
use app\models\Invoice;
use app\models\InvoiceMessage;
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
     * @param int $id id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $invoice = Helper::findOrFail(Invoice::blogValidQuery()->andWhere(['id' => $id]));

        $post = Yii::$app->request->post();
        $state = Yii::$app->request->get('state', '');
        $newStatus = Yii::$app->request->get('new_status');
        $invoiceMessage = new InvoiceMessage();

        if ($state) {
            $updateCacheNeeded = false;

            if ($state == 'setStatus' and mb_strlen($newStatus)) {
                $updateCacheNeeded = (bool) $invoice->setNewStatus($newStatus);
            } else if ($state == 'newMessage' and $invoiceMessage->load($post)) {
                $updateCacheNeeded = (bool) InvoiceMessage::createInvoiceMessage($invoice->blog_name, $invoice->id, $invoiceMessage->message, false);
            }

            return $this->redirect(['invoice/view', 'id' => $id]);
        }

        return $this->render('view', $invoice->invoiceFullResponse() + [
            'invoiceMessageModel' => $invoiceMessage,
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
        if (($model = Invoice::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
