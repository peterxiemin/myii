<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\EnvSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '环境列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="env-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('创建新环境', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn', 'visible' => false],
            [
                'attribute' => 'id',
                'format' => 'text',
                'filter' => false,
            ],
            'env_name',
            'branch_name',
            'hostname',
            'path',
            [
                'attribute' => 'port',
                'format' => 'text',
                'filter' => false,
            ],

            //'agent_url',
            [
                'attribute' => 'discription',
                'format' => 'text',
                'filter' => false,
            ],
	    'status',
            ['class' => 'yii\grid\ActionColumn', 'header'=>'操作', 'template' => '{view} {edit-file} {clone}', 'buttons' => [
                'edit-file' => function ($url, $model, $key) {
                    return  Html::a('<span class="glyphicon glyphicon-cog"></span>', $url, ['title' => '编辑配置文件'] ) ;
                },
                'clone' => function ($url, $model, $key) {
                    if ($model->branch_name == 'master')
                        return  Html::a('<span class="glyphicon glyphicon-export"></span>', $url, ['title' => '克隆分支'] ) ;
                },]
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
