<?php

/**
 * Created by PhpStorm.
 * User: xiemin02
 * Date: 2016/8/11
 * Time: 15:23
 */
namespace app\library\util\http;
use app\library\myconst\ErrorInfo;
use linslin\yii2\curl\Curl;

class Ral
{
    public static function requestUrl($url, $params)
    {
        $curl = new Curl();
        $curl->setOptions(
            [
                CURLOPT_POSTFIELDS =>
                    http_build_query($params),
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 30
            ]);
        try {
            $ret = $curl->post($url);
        } catch (\Exception $e) {
            return [
                'err' => true,
                'data' => json_encode(array(
                    'errno' => ErrorInfo::ERR_CURL_FAILD_ERROR,
                    'msg' => 'request agent failed. code=' . $e->getCode() . ' message=' . $e->getMessage(),
                ))
            ];
        }
        if ($curl->responseCode != 200) {
            return [
                'err' => true,
                'data' => json_encode(array(
                    'errno' => ErrorInfo::ERR_CURL_FAILD_ERROR,
                    'msg' => 'request agent failed. responseCode=' . $curl->responseCode,
                ))
            ];
        }

        return ['err' => false, 'data' => $ret];
    }
}
