<?php

namespace app\controllers;

use app\components\Cache;
use app\components\Helper;
use app\models\Category;
use app\models\CategorySearch;
use app\models\Gallery;
use app\models\Package;
use app\models\Product;
use app\models\ProductField;
use app\models\ProductSearch;
use app\models\TextArea;
use Yii;
use yii\web\UploadedFile;

class ProductController extends Controller
{

    public function behaviors()
    {
        return $this->defaultBehaviors([
            [
                'actions' => ['index',],
                'allow' => true,
                'verbs' => ['POST', 'GET'],
                'roles' => ['@'],
            ]
        ]);
    }

    public function actionIndex($parent_id, $id = null)
    {
        $id = empty($id) ? null : intval($id);
        $parent_id = intval($parent_id);
        $post = Yii::$app->request->post();
        $state = Yii::$app->request->get('state', '');
        $updateCacheNeeded = false;
        $oldStatus = null;
        $textAreaFields = new TextArea();
        $textAreaProducts = new TextArea();
        //
        if ($id) {
            $model = Helper::findOrFail(Product::blogValidQuery($id)->andWhere(['category_id' => $parent_id]));
        } else {
            $model = null;
        }
        $searchModel = new ProductSearch();
        $parentModel = Helper::findOrFail(Category::blogValidQuery()->andWhere(['id' => $parent_id]));
        $parentSearchModel = new CategorySearch();
        //
        if ($state == 'batchSave' && $textAreaProducts->load($post)) {
            $updateCacheNeeded = (bool) Product::batchSave($textAreaProducts, $parentModel);
            if (empty($textAreaProducts->explodeLines())) {
                $textAreaProducts = new TextArea();
            }
        } elseif ($state == 'update' && $model) {
            $oldStatus = $model->status;
            $updateCacheNeeded = Helper::store($model, $post, [
                'blog_name' => Yii::$app->user->getId(),
            ]);
        } elseif ($state == 'saveFields' && $model && $textAreaFields->load($post)) {
            $errors = ProductField::batchSave($textAreaFields->explodeLines(), $model);
            if ($errors) {
                $textAreaFields->addErrors(['values' => $errors]);
            } else {
                $textAreaFields = new TextArea();
                Cache::updateProductCacheField($model);
                $updateCacheNeeded = true;
            }
        } elseif ($state == 'status' && $model) {
            $model->status = Yii::$app->request->get('status', '');
            $updateCacheNeeded = $model->save();
        } elseif ($state == 'remove' && $model) {
            $packages = Package::blogValidQuery()->andWhere(['product_id' => $id])->all();
            if ($packages) {
                $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($packages), 'child' => Yii::t('app', 'Package'), 'parent' => Yii::t('app', 'Product')]);
                Yii::$app->session->setFlash('danger', $msg);
            } else {
                $updateCacheNeeded = Helper::delete($model);
            }
        } elseif ($state == 'galleryUpload' && $model) {
            if ($model->picture = UploadedFile::getInstance($model, 'picture')) {
                $gallery = Gallery::upload($model->picture->tempName, Gallery::TYPE_PRODUCT, $id, [
                    'mode' => Yii::$app->request->post('mode')
                ]);
                if ($gallery->hasErrors()) {
                    $model->addErrors(['picture' => $gallery->getErrorSummary(true)]);
                } else {
                    if (empty($model->image)) {
                        $model->image = $gallery->name;
                        $model->save();
                    }
                    $model->picture = null;
                }
            }
        } elseif ($state == 'galleryDelete' && $model && ($name = Yii::$app->request->get('name'))) {
            $gallery = Gallery::find()->where([
                'AND',
                ['name' => $name],
                ['product_id' => $id],
                ['type' => Gallery::TYPE_PRODUCT],
            ])->one();
            if ($gallery) {
                $gallery->delete();
            }
        } elseif ($state == 'galleryDefault' && $model && ($name = Yii::$app->request->get('name'))) {
            $gallery = Gallery::find()->where([
                'AND',
                ['name' => $name],
                ['product_id' => $id],
                ['type' => Gallery::TYPE_PRODUCT],
            ])->one();
            if ($gallery) {
                $model->image = $gallery->name;
                $model->save();
            }
        } else {
            $state = '';
        }
        if ($updateCacheNeeded) {
            Cache::updateCategoryCacheOptions($parentModel);
            if ($oldStatus !== null && $oldStatus != $model->status) {
                Cache::updateCacheParentsActiveStatus($model);
            }
        }
        //
        $autoCompleteSource = array_keys(Cache::getCategoryCacheOptions($parentModel));
        $autoCompleteSource = array_map('strval', $autoCompleteSource);
        $autoCompleteSource = array_fill_keys($autoCompleteSource, []);
        //

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $parentModel);
        return $this->render('index', [
            'state' => $state,
            'textAreaFields' => $textAreaFields,
            'textAreaProducts' => $textAreaProducts,
            'autoCompleteSource' => $autoCompleteSource,
        ] + compact('searchModel', 'parentModel', 'parentSearchModel', 'model', 'dataProvider'));
    }
}
