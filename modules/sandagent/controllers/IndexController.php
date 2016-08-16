<?php

namespace app\modules\sandagent\controllers;

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
	echo json_encode(array('errno'=>0, 'data'=>'success'));
    }
}
