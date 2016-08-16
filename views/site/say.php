<?php
use yii\helpers\Html;
use yii\jui\DatePicker;

?>
<div>
    <?= Html::encode($message) ?>
</div>
<div>
    <?= DatePicker::widget(['name' => 'date']) ?>
</div>
