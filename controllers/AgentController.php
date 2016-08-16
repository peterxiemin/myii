<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 16/7/24
 * Time: 17:38
 */

namespace app\controllers;

use app\library\util\http\Ral;
use Yii;
use app\models\Env;
use app\library\sandconsole\controllers\SandConsoleBaseController;


class AgentController extends SandConsoleBaseController
{
    const ERR_PARAM_EMPTY = 1000;
    const ERR_ENV_NOT_FOUND = 1002;
    const ERR_REQUEST_AGENT_FAILED = 1003;
    const ERR_PATH_DENY = 1004;
    const ERR_CONFIG_ERR = 1005;
    const ERR_DB_ERR = 1006;
    const STATUS_PRE_DEPLOY = 0;
    const STATUS_ING_DEPLOY = 1;
    const STATUS_AFT_DEPLOY = 2;

    public function actionIndex() {
	echo json_encode(array('errno'=>0, 'data'=>'success'));
    }

    public function actionLs($id)
    {
        $model = Env::findOne($id);
        if (!$model) {
            return $this->errorData(self::ERR_ENV_NOT_FOUND, 'env not found');
        }

        $path = Yii::$app->request->getQueryParam('path');
        if($path && !$this->startwith($path, $model->path)){
            return $this->errorData(self::ERR_PATH_DENY, 'path was denied');
        }

        $url = $model->agent_url;
        $result = $this->requestAgent($url, array(
            'action' => 'ls',
            'path' => $path ? $path : $model->path,
        ));
        if($result['err']){
            return $result['data'];
        }

        return $result['data'];
    }

    public function actionReadFile($id){
        $model = Env::findOne($id);
        if (!$model) {
            return $this->errorData(self::ERR_ENV_NOT_FOUND, 'env not found');
        }

        $path = Yii::$app->request->getQueryParam('path');
        if(empty($path)){
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'path' is empty");
        }
        if($path && !$this->startwith($path, $model->path)){
            return $this->errorData(self::ERR_PATH_DENY, 'path was denied');
        }

        $url = $model->agent_url;
        $result = $this->requestAgent($url, array(
            'action' => 'readFile',
            'path' => $path,
        ));

        if($result['err']){
            return $result['data'];
        }

        return $result['data'];
    }

    public function actionWriteFile($id)
    {
        $model = Env::findOne($id);
        if (!$model) {
            return $this->errorData(self::ERR_ENV_NOT_FOUND, 'env not found');
        }

        $path = Yii::$app->request->getQueryParam('path');
        $content = Yii::$app->request->getBodyParam('content');
        if(empty($path)){
            return $this->errorData(self::ERR_PARAM_EMPTY, "param 'path' is empty");
        }
        if($path && !$this->startwith($path, $model->path)){
            return $this->errorData(self::ERR_PATH_DENY, 'path was denied');
        }

        $url = $model->agent_url;
        $result = $this->requestAgent($url, array(
            'action' => 'writeFile',
            'path' => $path,
            'content' => $content,
        ));

        if($result['err']){
            return $result['data'];
        }

        return $result['data'];
    }

    public function actionDelCopy($id) {
        //发送远端清除命令
        $model = $this->findModel($id);
        $url = $model->agent_url;
        $ret = $this->requestAgent($url, array(
            'action' => 'delcopy',
            'uname'=> $model->branch_name,
            'path' => $model->path,
        ));
        //网络异常判断
        if ($ret['err']) {
            return $ret['data'];
        }
        return $ret['data'];
    }

    public function actionClone($id)
    {
        $sand_config = Yii::$app->params['sandEnv'];
        if (!$sand_config) {
            $this->errorData(self::ERR_CONFIG_ERR, 'actionClone get config failed');
        }
        //从参数中获取
        $branch_name = Yii::$app->request->getQueryParam('branch_name');
        $discription = Yii::$app->request->getQueryParam('discription');

        $env_name = $sand_config['env_name'];
        $hostname = $sand_config['hostname'];
        $path = $sand_config['path'];
        $url = $sand_config['agent_url'];
        $port = $this->getPort();
        if (!$port) {
            $this->errorData(self::ERR_DB_ERR, 'actionClone get max port failed');
        }
        $ret   = $this->requestAgent($url, array(
            'action' => 'clone',
            'uname'  => trim($branch_name),
            'path'   => trim($path),
            'port'   => trim($port),
        ));

        //网络异常判断
        if ($ret['err']) {
            return $ret['data'];
        }

        //逻辑判断
        if (!is_array($ret['data'])) {
            $err = json_decode($ret['data'], true);
            if (isset($err['errno']) && !$err['errno']) {
                $data = array(
                    'env_name' => $env_name,
                    'branch_name' => $branch_name,
                    'hostname' => $hostname,
                    'path' => $path . '_' . $branch_name,
                    'port' => $port,
                    'agent_url' => $url,
                    'discription' => $discription,
                    'status' => self::STATUS_ING_DEPLOY,
                );
                $model = new Env();
                $model->attributes = $data;
                $model->save();
            }
        }

        return $ret['data'];
    }

    /**
     * @param $id
     * @return mixed
     */
    public function actionUpdateCode($id) {
        $model = Env::findOne($id);
        $uname = $model->branch_name;
        $url   = $model->agent_url;
        $path  = $model->path;
        $app   = Yii::$app->request->getQueryParam('app');
        $svn   = Yii::$app->request->getQueryParam('svn');

        $ret = $this->requestAgent($url, array(
            'action' => 'updateCode',
            'uname'  => trim($uname),
            'path'   => trim($path),
            'app'    => trim($app),
            'svn'    => trim($svn),
        ));

        if($ret['err']){
            return $ret['data'];
        }

        return $ret['data'];
    }

    /**
     * @param $str
     * @param $pattern
     * @return bool
     */
    private function startwith($str, $pattern)
    {
        if (strpos($str, $pattern) === 0)
            return true;
        else
            return false;
    }

    /**
     * @return bool|mixed
     */
    private function getPort()
    {
        try {
            $row = Env::find()->orderBy('port desc')->one();
        } catch (\Exception $e) {
            return false;
        }
        return $row['port'] + 1;
    }

    /**
     * @param $url
     * @param $params
     * @return array
     */
    private function requestAgent($url, $params) {
        return Ral::requestUrl($url, $params);
    }

}
