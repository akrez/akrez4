<?php

namespace app\controllers;

use yii\data\Pagination;
use app\models\ProductField;
use app\components\SingleSort;
use app\models\FinancialAccount;
use app\components\Cache;
use app\components\Sms;
use app\models\Cart;
use app\models\Blog;
use app\models\Category;
use app\models\Customer;
use app\models\Field;
use app\models\FieldList;
use app\models\Gallery;
use app\models\Invoice;
use app\models\InvoiceItem;
use app\models\Language;
use app\models\LogApi;
use app\models\Package;
use app\models\Page;
use app\models\Product;
use app\models\City;
use app\models\Delivery;
use app\models\InvoiceMessage;
use app\models\Payment;
use app\models\Search;
use app\models\Status;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class Api1Controller extends Api
{
    private static $_blog = false;
    private static $_customer = false;

    public function init()
    {
        parent::init();
        Yii::$app->user->loginUrl = null;
        Yii::$app->user->enableSession = false;
        Yii::$app->user->enableAutoLogin = false;
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->charset = 'UTF-8';
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            $statusCode = $event->sender->statusCode;
            $data = $event->sender->data;
            //
            if (Yii::$app->response->format == Response::FORMAT_JSON) {
                $data = (array) $data;
                $event->sender->data = [
                    '_constant_hash' => self::CONSTANT_HASH,
                    '_blog'          => (self::blog() ? self::blog()->toArray() : []),
                    '_categories'    => self::categories(),
                    '_customer'      => (Yii::$app->customerApi->getIdentity() ? Yii::$app->customerApi->getIdentity()->toArray() : []),
                ];
                if ($statusCode == 200 && isset($data['code'])) {
                    $event->sender->data['_code'] = $data['code'];
                } else {
                    $event->sender->data['_code'] = $statusCode;
                }
                if ($statusCode == 200 || YII_DEBUG) {
                    $event->sender->data += $data;
                }
            } else {
                if ($statusCode == 200 || YII_DEBUG) {
                } else {
                    $event->sender->data = null;
                }
            }
        });
        if (empty(self::blog())) {
            Api::exceptionNotFoundHttp();
        }
        Yii::$app->language = self::blog()->language;
    }

    public static function blog()
    {
        if (self::$_blog !== false) {
            return self::$_blog;
        }

        self::$_blog = null;

        $blogName = Yii::$app->request->get(self::BLOG_PARAM, null);
        if ($blogName) {
            $blog = Blog::findBlogForApi($blogName);
            if ($blog) {
                self::$_blog = $blog;
            }
        }

        return self::$_blog;
    }

    /**
     * @return Customer|null
     */
    public static function customer()
    {
        if (self::$_customer !== false) {
            return self::$_customer;
        }

        self::$_customer = null;

        if (Yii::$app->customerApi->getIdentity()) {
            self::$_customer = Yii::$app->customerApi->getIdentity();
        }

        return self::$_customer;
    }

    public static function categories()
    {
        $blog = self::blog();
        if ($blog) {
            return Cache::getBlogCacheCategory($blog);
        }
        return [];
    }

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => 'yii\filters\auth\QueryParamAuth',
                'user' => Yii::$app->customerApi,
                'optional' => ['*'],
                'tokenParam' => self::TOKEN_PARAM,
            ],
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'user' => Yii::$app->customerApi,
                'denyCallback' => function ($rule, $action) {
                    Api::exceptionForbiddenHttp();
                },
                'rules' => [
                    [
                        'actions' => ['constant', 'index', 'category', 'product', 'info', 'page'],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'signout', 'profile',
                            'cart', 'cart-add', 'cart-delete',
                            'invoice-submit', 'invoice-add', 'invoice-view', 'invoice-remove', 'invoices', 'invoice-view',
                            'delivery-add', 'delivery-view', 'delivery-edit', 'delivery-delete', 'deliveries',
                            'payment-add', 'payment-delete', 'payments',
                            'invoice-message-create',
                        ],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'verify-request', 'verify', 'reset-password-request', 'reset-password'],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }


    public function actionInfo()
    {
        return [];
    }

    public static function actionConstant()
    {
        $result = [];
        foreach (Language::getList() as $languageKey => $language) {
            Yii::$app->language = $languageKey;
            $result[$languageKey] = [
                'widget' => FieldList::widgetsList(),
                'opertaion' => FieldList::opertaionsList(),
                'city' => City::getList(),
                'language' => Language::getList(),
                'entity_page' => Page::entityPage(),
                'invoice_valid_statuses' => Invoice::validStatuses(),
                'financial_account_identity_type' => FinancialAccount::getTypeList(),
            ];
        }
        return ['constant' => $result];
    }

    public function actionIndex()
    {
        return $this->search(Yii::$app->request->post());
    }

    public function actionCategory($category_id)
    {
        return $this->search(Yii::$app->request->post(), $category_id);
    }

    public function search($options = [], $category_id = null)
    {
        $options = (array)$options + [
            'page' => null,
            'page_size' => null,
            'sort' => null,
        ];
        $page = $options['page'];
        $page_size = $options['page_size'];
        $sort = $options['sort'];
        //
        $blog = self::blog();
        //
        $sortAttributes = [
            'view' => Yii::t('app', 'Most Viewed'),
            '-created_at' => Yii::t('app', 'Newest'),
            'created_at' => Yii::t('app', 'Oldest'),
            '-title' => Yii::t('app', 'Title (Desc)'),
            'title' => Yii::t('app', 'Title (Asc)'),
        ];
        ////
        $query = Product::findProductQueryForApi($blog->name);
        //
        $categories = self::categories();
        //
        if (empty($category_id)) {
            $category = null;
            $query->andWhere(['category_id' => array_keys($categories),]);
            $category_id = null;
        } else {
            if (!isset($categories[$category_id])) {
                Api::exceptionNotFoundHttp();
            }
            $category = Category::findCategoryForApi($blog->name, $category_id);
            if (!$category) {
                Api::exceptionNotFoundHttp();
            }
            $query->andWhere(['category_id' => $category_id,]);
        }
        //
        $allowedFieldsOfSections = [
            'Product' => ['title'],
            'Package' => ['price'],
            'ProductField' => boolval($category_id),
        ];
        //
        $search = [];
        $conditionsOfSections = [];
        foreach ($allowedFieldsOfSections as $section => $allowedFields) {
            $search[$section] = [];
            $conditionsOfSections[$section] = [];
            $postedDatas = (array)Yii::$app->request->post($section, []);
            foreach ($postedDatas as $postedField => $postedValues) {
                if ($allowedFields === true) {
                } elseif ($allowedFields === false) {
                    continue;
                } elseif (!in_array($postedField, $allowedFields)) {
                    continue;
                }
                $postedValues = (array)$postedValues;
                foreach ($postedValues as $postedValue) {
                    $searchModel = new Search();
                    $searchModel->load($postedValue, '');
                    $searchModel->field = $postedField;
                    if ($searchModel->validate()) {
                        $search[$section][$postedField][] = $searchModel->toArray();
                        //
                        $condition = [];
                        $condition[0] = $searchModel->operation;
                        $condition[1] = $searchModel->field;
                        if (in_array($searchModel->operation, FieldList::getPluralOperations())) {
                            $condition[2] = $searchModel->values;
                        } elseif (in_array($searchModel->operation, FieldList::getMinMaxOperations())) {
                            $condition[2] = $searchModel->value_min;
                            $condition[3] = $searchModel->value_max;
                        } else {
                            $condition[2] = $searchModel->value;
                        }
                        //
                        if ($section == 'ProductField') {
                            $condition[1] = 'value';
                            $condition = [
                                'AND',
                                ['blog_name' => $blog->name],
                                ['field' => $searchModel->field],
                                $condition,
                            ];
                            $conditionsOfSections[$section][] = [
                                'id' => ProductField::find()->select('product_id')->where($condition),
                            ];
                        } else {
                            $conditionsOfSections[$section][] = $condition;
                        }
                    }
                }
            }
        }

        if ($conditionsOfSections['Product']) {
            array_unshift($conditionsOfSections['Product'], 'AND');
            $query->andWhere($conditionsOfSections['Product']);
        } elseif ($conditionsOfSections['Package']) {
            array_unshift($conditionsOfSections['Package'], 'AND');
            $query->andWhere([
                'id' => Package::find()->select('product_id')->where(['status' => Status::STATUS_ACTIVE])->where($conditionsOfSections['ProductField']),
            ]);
        } elseif ($conditionsOfSections['ProductField']) {
            array_unshift($conditionsOfSections['ProductField'], 'AND');
            $query->andWhere($conditionsOfSections['ProductField']);
        }

        $products = [];
        $countOfResults = $query->count('id');

        $singleSort = new SingleSort([
            'sort' => $sort,
            'sortAttributes' => $sortAttributes,
        ]);

        $page_size = intval($page_size);
        if ($page_size == -1) {
            $page_size = $countOfResults;
        } elseif ($page_size > 0) {
            $page_size = $page_size;
        } else {
            $page_size = 12;
        }

        $pagination = new Pagination([
            'params' => [
                'page' => $page,
                'per-page' => $page_size,
            ],
            'totalCount' => $countOfResults,
        ]);

        if ($countOfResults > 0) {
            $products = $query->orderBy([$singleSort->attribute => $singleSort->order])->offset($pagination->offset)->limit($pagination->limit)->all();
            $products = ArrayHelper::toArray($products);
        }

        return [
            'categoryId' => $category_id,
            'category' => $category,
            'products' => $products,
            'fields' => Field::getFieldsList($category_id),
            'sort' => [
                'attribute' => $singleSort->sort,
                'attributes' => $singleSort->sortAttributes,
            ],
            'pagination' => [
                'page_count' => $pagination->getPageCount(),
                'page_size' => $pagination->getPageSize(),
                'page' => $pagination->getPage(),
                'total_count' => $countOfResults,
            ],
            'options' => ($category ? Cache::getCategoryCacheOptions($category) : []),
        ] + $search;
    }

    public function actionProduct($id)
    {
        $blog = self::blog();
        $categories = self::categories();

        $product = Product::findProductQueryForApi($blog->name)->andWhere([
            'AND', [
                'id' => $id,
                'category_id' => array_keys($categories)
            ]
        ])->one();
        if ($product) {
            $product->increaseView();
            $product = $product->toArray();
        } else {
            Api::exceptionNotFoundHttp();
        }

        $fields = Field::getFieldsList($product['category_id']);

        $images = Gallery::findProductGalleryQueryForApi($blog->name, $product['id'])->indexBy('name')->all();

        $packages = Package::findPackageFullQueryForApi($blog->name)
            ->andWhere(['product_id' => $product['id']])
            ->all();

        LogApi::setData(['model_category_id' => $product['category_id']]);

        return [
            'categoryId' => $product['category_id'],
            'product' => $product,
            'fields' => $fields,
            'images' => ArrayHelper::toArray($images),
            'packages' => ArrayHelper::toArray($packages),
        ];
    }

    public function actionPage($entity = null, $page_type = null, $entity_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        $blog = self::blog();
        $page = Page::findPageQueryForApi($blog->name, $entity, $page_type, $entity_id)->one();
        if (!$page) {
            Api::exceptionNotFoundHttp();
        }
        return $page->body;
    }

    public static function actionCartAdd($package_id, $cnt = 1, $add = true)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $package = Package::findPackageFullQueryForApi($blog->name)->andWhere(['id' => $package_id])->one();
        if (!$package) {
            Api::exceptionNotFoundHttp();
        }

        $cart = Cart::findCartQueryForApi($blog->name, $customer->id)->andWhere(['package_id' => $package_id])->one();
        if (empty($cart)) {
            $cart = new Cart();
            $cart->price_initial = $package->price;
            $cart->cnt = 0;
            $cart->package_id = $package->id;
            $cart->product_id = $package->product_id;
            $cart->customer_id = $customer->id;
            $cart->blog_name = $blog->name;
            $cart->cache_parents_active_status = Cache::calcCacheParentsActiveStatus($package);
        }

        $cnt = (int) $cnt;
        $cart->cnt = ($add ? $cart->cnt + $cnt : $cnt);

        $cart->save();
        return [
            'package' => $package,
            'cart' => Cart::packageValidationResponse($cart, $package),
        ];
    }

    public static function actionCart()
    {
        try {
            $blog = self::blog();
            $customer = self::customer();
            return Cart::cartResponse($blog->name, $customer->id, false) + [
                'deliveries' => Delivery::findDeliveryQueryForApi($blog->name, $customer->id)
                    ->orderBy(['id' => SORT_DESC])
                    ->all(),
                'payments' => Payment::findPaymentQueryForApi($blog->name, $customer->id, null)
                    ->orderBy(['id' => SORT_DESC])
                    ->all(),
            ];
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp($e);
        }
    }

    public static function actionInvoiceMessageCreate($invoice_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $message = \Yii::$app->request->post('message');
        //
        $invoice = Invoice::findInvoiceQueryForApi($blog->name, $customer->id)->andWhere(['id' => $invoice_id])->one();
        if (!$invoice) {
            Api::exceptionNotFoundHttp();
        }

        $invoiceMessage = InvoiceMessage::createInvoiceMessage($blog->name, $invoice->id, $message, true);
        return [
            'invoiceMessage' => $invoiceMessage->invoiceMessageResponse(),
        ];
    }

    public static function actionInvoiceSubmit()
    {
        $blog = self::blog();
        $customer = self::customer();
        $post = \Yii::$app->request->post();

        $invoice = new Invoice();
        $invoice->load($post, '');
        $invoice->blog_name = $blog->name;
        $invoice->customer_id = $customer->id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($invoice->save()) {
                foreach ($invoice->_carts['carts'] as $cartModel) {
                    $invoiceItem = InvoiceItem::forge(
                        $invoice,
                        $cartModel,
                        $invoice->_carts['packages'][$cartModel->package_id],
                        $invoice->_carts['products'][$invoice->_carts['packages'][$cartModel->package_id]->product_id]
                    );
                    if ($invoiceItem->save()) {
                        $cartModel->delete();
                    }
                }
                foreach ($invoice->_payments as $payment) {
                    $payment->invoice_id = $invoice->id;
                    $payment->save();
                }
                $delivery = Delivery::clone($invoice->_delivery, $invoice->id);
                if (!$delivery->hasErrors() && $invoice->saveDeliveryId($delivery)) {
                    $transaction->commit();
                }
                $invoice->setNewStatus(Invoice::STATUS_PENDING);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Api::exceptionBadRequestHttp($e);
        }

        return $invoice->invoiceFullResponse();
    }

    public function actionInvoices()
    {
        $page = Yii::$app->request->post('page');
        $page_size = Yii::$app->request->post('page_size');
        //
        $blog = self::blog();
        $customer = self::customer();
        //
        $query = Invoice::findInvoiceQueryForApi($blog->name, $customer->id);
        //
        $countOfResults = $query->count('id');
        //
        $page_size = intval($page_size);
        if ($page_size == -1) {
            $page_size = $countOfResults;
        } elseif ($page_size > 0) {
            $page_size = $page_size;
        } else {
            $page_size = 12;
        }

        $pagination = new Pagination([
            'params' => [
                'page' => $page,
                'per-page' => $page_size,
            ],
            'totalCount' => $countOfResults,
        ]);

        $invoices = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $invoices = array_map(function ($invoice) {
            return $invoice->invoiceFullResponse();
        }, $invoices);

        return [
            'invoices' => $invoices,
            'pagination' => [
                'page_count' => $pagination->getPageCount(),
                'page_size' => $pagination->getPageSize(),
                'page' => $pagination->getPage(),
                'total_count' => $countOfResults,
            ],
        ];
    }

    public function actionInvoiceView($invoice_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $invoice = Invoice::findInvoiceQueryForApi($blog->name, $customer->id)->andWhere(['id' => $invoice_id])->one();
        if (!$invoice) {
            Api::exceptionNotFoundHttp();
        }
        //
        return $invoice->invoiceFullResponse();
    }

    public static function actionCartDelete($package_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $status = false;
        $cart = Cart::findCartQueryForApi($blog->name, $customer->id)->andWhere(['package_id' => $package_id])->one();
        if ($cart) {
            $status = $cart->delete();
        }

        return [
            'status' => $status,
        ];
    }

    public static function actionPayments($invoice_id = null)
    {
        $blog = self::blog();
        $customer = self::customer();
        $invoice_id = ($invoice_id ? $invoice_id : null);
        //
        $query = Payment::findPaymentQueryForApi($blog->name, $customer->id, $invoice_id)
            ->orderBy(['id' => SORT_DESC]);

        return [
            'payments' => $query->all(),
        ];
    }

    public static function actionPaymentDelete($payment_id = null)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $payment = Payment::findPaymentQueryForApi($blog->name, $customer->id, null)
            ->andWhere(['id' => $payment_id])
            ->one();

        if ($payment) {
        } else {
            Api::exceptionNotFoundHttp();
        }

        return [
            'status' => $payment->delete(),
        ];
    }

    public static function actionPaymentAdd($invoice_id = null)
    {
        $blog = self::blog();
        $customer = self::customer();
        $post = \Yii::$app->request->post();
        $invoice_id = ($invoice_id ? $invoice_id : null);

        //bypass max_allowed_packet sql error
        LogApi::setData(['data_post' => json_encode([
            'payment_name_file' => 'bypass max_allowed_packet error - set in ' . Yii::$app->controller->id . '-' . Yii::$app->controller->action->id . ' manually',
        ] + $post)]);

        $payment = new Payment();
        $payment->load($post, '');
        $payment->blog_name = $blog->name;
        $payment->customer_id = $customer->id;
        $payment->invoice_id = $invoice_id;

        $payment->upload();
        $payment->save();

        return [
            'payment' => $payment->paymentResponse(),
        ];
    }

    public static function actionDeliveries()
    {
        $page = Yii::$app->request->post('page');
        $page_size = Yii::$app->request->post('page_size');
        //
        $blog = self::blog();
        $customer = self::customer();
        //
        $query = Delivery::findDeliveryQueryForApi($blog->name, $customer->id)
            ->orderBy(['id' => SORT_DESC]);
        //
        $countOfResults = $query->count('id');
        //
        $page_size = intval($page_size);
        if ($page_size == -1) {
            $page_size = $countOfResults;
        } elseif ($page_size > 0) {
            $page_size = $page_size;
        } else {
            $page_size = 12;
        }

        $pagination = new Pagination([
            'params' => [
                'page' => $page,
                'per-page' => $page_size,
            ],
            'totalCount' => $countOfResults,
        ]);

        $deliveries = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [
            'deliveries' => $deliveries,
            'pagination' => [
                'page_count' => $pagination->getPageCount(),
                'page_size' => $pagination->getPageSize(),
                'page' => $pagination->getPage(),
                'total_count' => $countOfResults,
            ],
        ];
    }

    public static function actionDeliveryDelete($delivery_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $delivery = Delivery::findDeliveryQueryForApi($blog->name, $customer->id)
            ->andWhere(['id' => $delivery_id])
            ->one();

        if ($delivery) {
        } else {
            Api::exceptionNotFoundHttp();
        }

        return [
            'status' => $delivery->delete(),
        ];
    }

    public static function actionDeliveryEdit($delivery_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        $post = \Yii::$app->request->post();
        //
        $delivery = Delivery::findDeliveryQueryForApi($blog->name, $customer->id)
            ->andWhere(['id' => $delivery_id])
            ->one();

        if ($delivery) {
        } else {
            Api::exceptionNotFoundHttp();
        }

        Delivery::storeAsTemplate($delivery, $post, $blog, $customer);

        return [
            'delivery' => $delivery->deliveryResponse(),
        ];
    }

    public static function actionDeliveryView($delivery_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        //
        $delivery = Delivery::findDeliveryQueryForApi($blog->name, $customer->id)
            ->andWhere(['id' => $delivery_id])
            ->one();

        if ($delivery) {
        } else {
            Api::exceptionNotFoundHttp();
        }

        return [
            'delivery' => $delivery->deliveryResponse(),
        ];
    }

    public static function actionDeliveryAdd()
    {
        $blog = self::blog();
        $customer = self::customer();
        $post = \Yii::$app->request->post();
        //
        $delivery = new Delivery();

        Delivery::storeAsTemplate($delivery, $post, $blog, $customer);

        return [
            'delivery' => $delivery->deliveryResponse(),
        ];
    }

    public function actionLogin()
    {
        $post = \Yii::$app->request->post();
        $blog = self::blog();
        //
        $signup = Customer::signup($blog->name, $post);
        if ($signup == null) {
            Api::exceptionBadRequestHttp();
        }
        //
        if (!$signup->hasErrors()) {
            return $signup->response('verify-request', true);
        }
        //
        $login = $signup->getCustomer();
        if (!$login) {
            return $signup->response('login', false);
        }
        $signup->clearErrors();
        //
        $login->setScenario('login');
        $login->load($post, '');
        if ($login->validate()) {
            return $login->response('index', true, true);
        }
        $signup->addErrors(['password' => $login->getErrorSummary(true)]);
        if ($login->status == Customer::SIGNUP_STATUS) {
            return $signup->response('verify-request', false);
        }
        return $signup->response('login', false);
    }

    public function actionVerifyRequest()
    {
        $blog = self::blog();
        //
        $verifyRequest = new Customer(['scenario' => 'verifyRequest']);
        try {
            $verifyRequest->load(\Yii::$app->request->post(), '');
            $verifyRequest->blog_name = $blog->name;
            if ($verifyRequest->validate()) {
                $user = $verifyRequest->getCustomer();
                $user->setAttributeToken('verify_token', 'verify_at');
                if ($user->save(false)) {
                    Sms::customerVerifyRequest($user, $blog);
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp($e);
        }
        return $verifyRequest->response();
    }

    public function actionVerify()
    {
        $blog = self::blog();
        $post = \Yii::$app->request->post();
        //
        $verify = new Customer(['scenario' => 'verify']);
        try {
            $verify->load($post, '');
            $verify->blog_name = $blog->name;
            if ($verify->validate()) {
                $user = $verify->getCustomer();
                $user->setAttributeToken('verify_token', 'verify_at', true);
                $user->status = Status::STATUS_ACTIVE;
                if ($user->save(false)) {
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp($e);
        }
        return $verify->response();
    }

    public function actionSignout()
    {
        $signout = Yii::$app->customerApi->getIdentity();
        if (!$signout) {
            Api::exceptionNotFoundHttp();
        }
        $signout->setAuthKey();
        if ($signout->save(false)) {
            return $signout->response();
        }
        Api::exceptionBadRequestHttp();
    }

    public function actionResetPasswordRequest()
    {
        $blog = self::blog();
        //
        $resetPasswordRequest = new Customer(['scenario' => 'resetPasswordRequest']);
        try {
            $resetPasswordRequest->load(\Yii::$app->request->post(), '');
            $resetPasswordRequest->blog_name = $blog->name;
            if ($resetPasswordRequest->validate()) {
                $user = $resetPasswordRequest->getCustomer();
                $user->setAttributeToken('reset_token', 'reset_at');
                if ($user->save(false)) {
                    Sms::customerResetPasswordRequest($user, $blog);
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp($e);
        }
        return $resetPasswordRequest->response();
    }

    public function actionResetPassword()
    {
        $blog = self::blog();
        //
        $resetPassword = new Customer(['scenario' => 'resetPassword']);
        try {
            $resetPassword->load(\Yii::$app->request->post(), '');
            $resetPassword->blog_name = $blog->name;
            if ($resetPassword->validate()) {
                $user = $resetPassword->getCustomer();
                $user->setAttributeToken('reset_token', 'reset_at', true);
                $user->setPasswordHash($resetPassword->password);
                if ($user->save(false)) {
                    return $user->response();
                }
            }
        } catch (Throwable $e) {
            Api::exceptionBadRequestHttp($e);
        }
        return $resetPassword->response();
    }
}
