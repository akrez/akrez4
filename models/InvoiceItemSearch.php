<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\InvoiceItem;

/**
 * InvoiceItemSearch represents the model behind the search form of `app\models\InvoiceItem`.
 */
class InvoiceItemSearch extends InvoiceItem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'cnt', 'package_id', 'product_id', 'customer_id', 'category_id', 'invoice_id'], 'integer'],
            [['title', 'code', 'image', 'color_code', 'params'], 'safe'],
            [['price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $invoice)
    {
        $query = InvoiceItem::blogValidQuery();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($invoice) {
            $query->andWhere([
                'invoice_id' => $invoice->id,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'price' => $this->price,
            'cnt' => $this->cnt,
            'package_id' => $this->package_id,
            'product_id' => $this->product_id,
            'customer_id' => $this->customer_id,
            'category_id' => $this->category_id,
            'invoice_id' => $this->invoice_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'color_code', $this->color_code])
            ->andFilterWhere(['like', 'params', $this->params])
            ->andFilterWhere(['like', 'blog_name', $this->blog_name]);

        return $dataProvider;
    }
}
