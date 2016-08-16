<?php
/**
 * Created by PhpStorm.
 * User: xiemin02
 * Date: 2016/8/11
 * Time: 14:26
 */

namespace app\library\sandconsole\controllers;
use Yii;
use yii\web\Controller;

class BaseController extends Controller {
    
    public $enableCsrfValidation = false;

    /**
     * @param $data
     * @return string
     */
    protected function successData($data)
    {
        return json_encode(
            array(
                'errno' => 0,
                'data' => $data,
            )
        );
    }

    /**
     * @param $errno
     * @param $msg
     * @return string
     */
    protected function errorData($errno, $msg)
    {
        return json_encode(
            array(
                'errno' => $errno,
                'msg' => $msg,
            )
        );
    }

    /**
     * @param $name
     * @param string $method
     * @return array|mixed
     */
    protected function getParams($name, $method = '') {
        if ($method == 'post') {
            return Yii::$app->request->post($name);
        }

        if ($method == 'get') {
            return Yii::$app->request->get($name);
        }

        $merage = array_merge(Yii::$app->request->get(), Yii::$app->request->post());
        return $merage[$name] ? $merage[$name] : $merage;
    }

    //加入验参功能
    protected function checkParams() {

    }

    //获取入口url路径
    protected function getIndexUrl() {
        return Yii::$app->request->hostInfo . Yii::$app->homeUrl;
    }
}
