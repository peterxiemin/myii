<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model app\models\Env */

$this->title = '克隆分支';
$this->params['breadcrumbs'][] = ['label' => '环境列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = $model->branch_name;
$this->registerJsFile('@web/js/app/env/clone.js', ['position' =>\yii\web\View::POS_END, 'depends' => [
    'app\assets\AppAsset'
]]);
?>
<div class="env-create">
    <?= Html::input('hidden', 'env_id', $model->id, ['id' => 'env_id'])?>
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="env-form">

        <?php $form = ActiveForm::begin(); ?>


        <?= $form->field($model, 'branch_name')->textInput(['maxlength' => true, ]) ?>

        <?= $form->field($model, 'discription')->textInput(['maxlength' => true, ]) ?>

        <div class="form-group">
            <button id="btn_submit" class="btn btn-success" type="button">提交</button>
            <?= Html::a('返回', ['index'], ['class' => 'btn btn-warning'])?>
        </div>

        <div class="modal fade" id="modal" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close"
                                data-dismiss="modal" aria-hidden="true">
                            &times;
                        </button>
                        <h4 class="modal-title" id="myModalLabel">
                            提示
                        </h4>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">关闭
                        </button>
                        <button type="button" class="modal-btn-ok hide btn btn-primary">
                            确认
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal -->
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
