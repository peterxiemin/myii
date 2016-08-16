<?php

namespace app\modules\sandagent\controllers;

use app\library\constant\ErrorInfo;
use yii\web\Controller;

/**
 * Default controller for the `sandagent` module
 */
class IndexController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        echo ErrorInfo::ERR_CONSOLE_SANDAGENT_DIR_FAILED;
    }
}
