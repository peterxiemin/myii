<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 16/8/2
 * Time: 15:38
 */

namespace app\controllers;

use Yii;
use yii\web\Controller;
use linslin\yii2\curl\Curl;
use app\library\sandconsole\controllers\SandConsoleBaseController;
use app\library\util\http\Ral;

class OuterController extends SandConsoleBaseController
{
    const URL_NUOMI_PLUS = 'http://nj03-orp-app0013.nj03.baidu.com:8118';
    const ERR_PARAM_EMPTY = 1000;
    const ERR_HTTP_REQUEST_FAILED = 1003;

/******************************************************* action start *************************************************/

    public function actionTest() {
	echo 'hello outer';
    }
    /**
     * 设置到店付TP信息
     * @return string
     */
    public function actionSetTp()
    {
        $orderType = Yii::$app->request->getQueryParam('order_type');
        $token = Yii::$app->request->getQueryParam('token');
        if (empty($orderType)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'order_type' should not be empty");
        }
        if (empty($token)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'token' should not be empty");
        }
        $ret = $this->setOpenApiTp($orderType, $token);
        return $ret['data'];
    }

    /**
     * 获取到店付TP信息
     * @return string
     */
    public function actionGetTp()
    {
        $orderType = Yii::$app->request->getQueryParam('order_type');
        if (empty($orderType)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'order_type' should not be empty");
        }
        $ret = $this->getOpenApiTp($orderType);
        return $ret['data'];
    }

    /**
     * 查询订单
     */
    public function actionQueryOrder()
    {
        $tp = Yii::$app->request->getQueryParam('tp');
        $tp_order_ids = Yii::$app->request->getQueryParam('tp_order_ids');
        $coupon_codes = Yii::$app->request->getQueryParam('coupon_codes');
        $merchant_id = Yii::$app->request->getQueryParam('merchant_id');

        if (empty($tp)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'tp' should not be empty");
        }
        if (empty($tp_order_ids)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'tp_order_ids' should not be empty");
        }
        if (empty($coupon_codes)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'coupon_codes' should not be empty");
        }
        if (empty($merchant_id)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'merchant_id' should not be empty");
        }

        $tokenRet = $this->getTpToken($tp);
        if($tokenRet['err']){
            return $tokenRet['data'];
        }

        $token = $tokenRet['data'];
        $sequence = time();
        $params = [
            'tp' => $tp,
            'tp_order_ids' => $tp_order_ids,
            'coupon_codes' => $coupon_codes,
            'merchant_id' => $merchant_id,
            'sequence' => $sequence,
        ];
        $params['sign'] = $this->getIntSign($params, $token);
        $ret = $this->requestUrl(self::nuomiPlusUrl('/paynow/openapi/queryorder'), $params);
        return $ret['data'];
    }

    /**
     * 设置门店Tag
     * @return string
     */
    public function actionSetMerchantTag()
    {
        $orderType = Yii::$app->request->getQueryParam('order_type');
        $merchant_ids = Yii::$app->request->getQueryParam('merchant_ids');
        if (empty($orderType)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'order_type' should not be empty");
        }
        if (empty($merchant_ids)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'merchant_ids' should not be empty");
        }

        $ret = $this->setPoiTagInT10Mis($orderType, $merchant_ids);
        return $ret['data'];
    }

    /**
     * 获取MIS配置
     * @return string
     */
    public function actionGetConfig(){
        $service = Yii::$app->request->getQueryParam('service');
        $config_ids = Yii::$app->request->getQueryParam('config_ids');
        if (empty($service)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'service' should not be empty");
        }
        if (empty($config_ids)) {
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'config_ids' should not be empty");
        }

        $ret = $this->getConfig($service, $config_ids);
        return $ret['data'];
    }
/********************************************************* action end *************************************************/

    /**
     * 获取TP的Token串
     * @param string $tp
     * @return array
     */
    private function getTpToken($tp){
        $tpRet = $this->getOpenApiTp($tp);
        if(!$tpRet['err']){
            $res = json_decode($tpRet['data'], true);
            if($res['data'] && $res['data']['configs'] && $res['data']['configs'][$tp]){
                $config = json_decode($res['data']['configs'][$tp]['config'], true);
                return [
                        'err' => false,
                        'data' => $config['token']
                ];
            }
        }
        return [
            'err' => true,
            'data' => $tpRet['data']
        ];
    }

    /**
     * 设置TP 信息(Token、回调）
     * @param int $order_type
     * @param string $token
     * @return array
     */
    private function setOpenApiTp($order_type, $token)
    {
        $tpInfo = [
            "$order_type" =>
                [
                    "token" => $token,
                    "time_out" => 300, //过期时间 300 秒
                    "notifyUrl" => self::nuomiPlusUrl('/paynow/na/ping'),
                    "jump_way" => "normal",
                    "$order_type" => 1,
                    "refundUrl" => self::nuomiPlusUrl('/paynow/na/ping'),
                ]
        ];
        return $this->setConfig(10001, $tpInfo);
    }

    /**
     * 获取TP 信息(Token、回调）
     * @param int $order_type
     * @return array
     */
    private function getOpenApiTp($order_type)
    {
        return $this->getConfig(10001, $order_type);
    }

    /**
     * 设置T10 MIS中的门店tag
     * @param $order_type
     * @param $merchant_ids
     * @return array
     */
    private function setPoiTagInT10Mis($order_type,$merchant_ids){
        $merchantIdArray = explode(',', $merchant_ids);
        $config = [];
        foreach($merchantIdArray as $idx => $merchant_id){
            $merchant_id = trim($merchant_id);
            if(is_numeric($merchant_id)){
                $config[$merchant_id] = [
                    "order_type" => $order_type,
                ];
            }
        }
        return $this->setConfig(10002, $config);
    }

    /**
     * 获取配置
     * @param int $service
     * @param string $configIds 多个逗号分隔
     * @return array
     */
    private function getConfig($service, $configIds){
        $arrParam = array(
            'config_ids' => $configIds,
            "service" => $service,
        );
        return $this->requestUrl(self::nuomiPlusUrl('/t10misplatform/config/getconfig'), $arrParam);
    }

    /**
     * 设置配置
     * @param int $service
     * @param string $config
     * @return array
     */
    private function setConfig($service, $config){
        $arrParam = array(
            "service" => $service,
            'configs' => is_array($config) ? json_encode($config) : $config,
            'operator_id' => 'sandconsole',
        );
        return $this->requestUrl(self::nuomiPlusUrl('/t10misplatform/config/setconfig'), $arrParam);
    }


    private function getIntSign($params, $sha1)
    {
        ksort($params, SORT_STRING);// 对参数的key以字母顺序排序
        $paramsArr = array();
        foreach ($params as $pkey => $pval ) {
            if ($pkey == "token" || $pkey == "log_id")
                continue;
            array_push($paramsArr, ($pkey . ":" . $pval));// 用冒号连结参数的key和val
        }
        $hashString  = implode("_", $paramsArr);// 用下划线连结各参数(key,val)对
        $hashString = $hashString  . "_" . $sha1;// 在最后加上有效的sha1值
        $intSign = sha1($hashString);
        return $intSign;
    }

    private static function nuomiPlusUrl($uri)
    {
        return self::URL_NUOMI_PLUS . $uri;
    }

    private function requestUrl($url, $params) {
	return Ral::requestUrl($url, $params);
    }
    //private function requestUrl($url, $params)
    //{
    //    $curl = new Curl();
    //    $curl->setOptions(
    //        [
    //            CURLOPT_POSTFIELDS =>
    //                http_build_query($params),
    //            CURLOPT_CONNECTTIMEOUT => 5
    //        ]);
    //    try {
    //        $ret = $curl->post($url);
    //    } catch (\Exception $e) {
    //        return [
    //            'err' => true,
    //            'data' => $this->errorData(self::ERR_HTTP_REQUEST_FAILED, 'request failed. code=' . $e->getCode() . ' message=' . $e->getMessage())
    //        ];
    //    }
    //    if ($curl->responseCode != 200) {
    //        return [
    //            'err' => true,
    //            'data' => $this->errorData(self::ERR_HTTP_REQUEST_FAILED, 'request failed. responseCode=' . $curl->responseCode)
    //        ];
    //    }
    //    return ['err' => false, 'data' => $ret];
    //}

    //private function successData($data)
    //{
    //    return json_encode(
    //        array(
    //            'errno' => 0,
    //            'data' => $data,
    //        )
    //    );
    //}

    //private function errorData($errno, $msg)
    //{
    //    return json_encode(
    //        array(
    //            'errno' => $errno,
    //            'msg' => $msg,
    //        )
    //    );
    //}
}
