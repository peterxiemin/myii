<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Env */

$this->title = '创建环境';
$this->params['breadcrumbs'][] = ['label' => '环境列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="env-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
