<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TpconfigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tpconfigs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tpconfig-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Tpconfig', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'tp_name',
            'order_type',
            'token',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
