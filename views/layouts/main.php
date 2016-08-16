<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '综合收银台沙盒环境',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => '内部联调环境', 'url' => ['/env/index']],
            ['label' => '外部联调环境',
                'items' => [
                    ['label'=> '配置TP Token', 'url'=> Yii::$app->homeUrl.'?r=tpconfig/index'],
                    //['label'=> '配置TP 门店tag', 'url'=> ''],
                    ['label'=> '配置TP 门店映射','url' => 'http://cq01-t10-rd.epc.baidu.com:8080/neibu/tpinfo', 'linkOptions' => ['target'=> '_blank']],
                ]
            ],
            ['label' => '帮助',
                'items' => [
                    ['label'=> '沙盒环境说明','url' => 'http://wiki.baidu.com/pages/viewpage.action?pageId=198957838', 'linkOptions', 'linkOptions' => ['target'=> '_blank']],
                    ['label'=> '外部沙盒环境使用说明','url' => 'http://wiki.baidu.com/pages/viewpage.action?pageId=204651671', 'linkOptions', 'linkOptions' => ['target'=> '_blank']],
                ],
            ],
            //['label' => 'About', 'url' => ['/site/about']],
            //['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'navbar-form'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; 糯米餐饮业务研发部 <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
