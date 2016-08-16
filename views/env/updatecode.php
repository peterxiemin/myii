<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Env */

$this->title = '更新代码';
$this->params['breadcrumbs'][] = ['label' => '环境列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('@web/js/app/env/bootstrap-select.min.js', ['position' => \yii\web\View::POS_END, 'depends' => [
    'app\assets\AppAsset',
]]);

$this->registerJsFile('@web/js/app/env/updatecode.js', ['position' =>\yii\web\View::POS_END, 'depends' => [
    'app\assets\AppAsset'
]]);

$this->registerCssFile('@web/css/bootstrap-select.min.css');

?>

<?= Html::input('hidden', 'env_id', $model->id, ['id' => 'env_id'])?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="env-create">
    <div class="col-lg-12">
        <div class="input-group">
            <select id ="update_code_select" class="selectpicker">
                <option value="paynow">paynow</option>
                <option value="tradecenter">tradecenter</option>
            </select>
            <input id = "svn" type="text" class="form-control" placeholder="svn 地址">
            <span class="input-group-btn">
            <button id="btn_save" class="btn btn-default" type="button">提交!</button>
        </span>
        </div><!-- /input-group -->
    </div><!-- /.col-lg-6 -->
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
</div>