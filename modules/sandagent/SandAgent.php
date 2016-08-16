<?php

namespace app\modules\sandagent;
use Yii;
/**
 * sandagent module definition class
 */
class SandAgent extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\sandagent\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\sandagent\commands';
        }
        // custom initialization code goes here
    }
}
