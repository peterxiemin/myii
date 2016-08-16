<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EnvSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="env-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'env_name') ?>

    <?= $form->field($model, 'branch_name') ?>

    <?= $form->field($model, 'hostname') ?>

    <?= $form->field($model, 'path') ?>

    <?php //$form->field($model, 'port') ?>

    <?php //$form->field($model, 'agent_url') ?>

    <div class="form-group">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
