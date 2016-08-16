<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TpconfigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'TP信息';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tpconfig-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('新建TP', ['create'], ['class' => 'btn btn-success']) ?>
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
