<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Tpconfig */

$this->title = '更新TP信息: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '更新TP信息', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tpconfig-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
