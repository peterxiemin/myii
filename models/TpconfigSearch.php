<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Tpconfig;

/**
 * TpconfigSearch represents the model behind the search form about `app\models\Tpconfig`.
 */
class TpconfigSearch extends Tpconfig
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_type'], 'integer'],
            [['tp_name', 'token', 'detail'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Tpconfig::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'order_type' => $this->order_type,
        ]);

        $query->andFilterWhere(['like', 'tp_name', $this->tp_name])
            ->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'detail', $this->detail]);

        return $dataProvider;
    }
}
