<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Env */

$this->title = '编辑文件';
$this->params['breadcrumbs'][] = ['label' => '环境列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = $model->branch_name;
$this->registerJsFile('@web/js/app/env/env.js', ['position' =>\yii\web\View::POS_END, 'depends' => [
    'app\assets\ZTreeAssets',
]]);

$this->registerCssFile('@web/css/editFile.css');
?>
<div class="env-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => array(
            'path' => $model->hostname . ':' . $model->path,
        ),
        'attributes' => [
            array(
                'attribute' => 'path',
                'label' => '部署路径',
            ),
        ],
    ]) ?>
    <?= Html::input('hidden', 'env_id', $model->id, ['id' => 'env_id'])?>
    <?= Html::tag('div', '', ['id' => 'tree', 'class' => 'ztree']) ?>
    <div id="editor">
        <input id="filePath" readonly="readonly">
        <textarea id="content" name="content"></textarea>
        <button id="btn_save" class="btn btn-primary">保存</button>
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
