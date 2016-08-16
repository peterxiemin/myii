<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Env */

$this->title = '修改环境: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '环境列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="env-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
