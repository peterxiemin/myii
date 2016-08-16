<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Tpconfig */

$this->title = 'TP信息';
$this->params['breadcrumbs'][] = ['label' => 'TP信息', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tpconfig-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
