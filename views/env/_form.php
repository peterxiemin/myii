<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Env */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="env-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'env_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'branch_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'hostname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'port')->textInput() ?>

    <?= $form->field($model, 'agent_url')->textInput() ?>

    <?= $form->field($model, 'discription')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '新增' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= $model->isNewRecord ? '' : Html::a('删除', ['delete', 'id' => 0],['class' => 'btn btn-danger', 'data' =>
            [
                'confirm' => '确认删除?',
                'method' => 'post',
            ]
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
