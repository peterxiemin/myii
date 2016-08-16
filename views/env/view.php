<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Env */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '环境列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="env-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '确认删除?',
                'method' => 'post',
            ],
        ]) ?>

        <?= Html::a('编辑配置文件', ['edit-file', 'id' => $model->id], ['class' => 'btn btn-warning'])?>
        <?= Html::a('更新代码', ['update-code', 'id' => $model->id], ['class' => 'btn btn-warning'])?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'env_name',
            'branch_name',
            'hostname',
            'path',
            'port',
            'agent_url',
            'discription',
        ],
    ]) ?>

</div>
