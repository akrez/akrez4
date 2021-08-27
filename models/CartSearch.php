<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Cart;

/**
 * CartSearch represents the model behind the search form of `app\models\Cart`.
 */
class CartSearch extends Cart
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'updated_at', 'created_at', 'cnt', 'package_id', 'customer_id', 'cache_parents_active_status'], 'integer'],
            [['price_initial'], 'number'],
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
    public function search($params)
    {
        $query = Cart::blogValidQuery();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'price_initial' => $this->price_initial,
            'cnt' => $this->cnt,
            'package_id' => $this->package_id,
            'customer_id' => $this->customer_id,
            'cache_parents_active_status' => $this->cache_parents_active_status,
        ]);

        $query->andFilterWhere(['like', 'blog_name', $this->blog_name]);

        return $dataProvider;
    }
}
