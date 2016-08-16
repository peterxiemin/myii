<?php
/**
 * Created by PhpStorm.
 * User: xiemin02
 * Date: 2016/8/16
 * Time: 14:49
 */
namespace app\controllers;
use app\library\sandconsole\controllers\SandConsoleBaseController;
use app\models\Env;

Class ApiAgentController extends SandConsoleBaseController {
    public function actionUpdateStatus() {
        $hostname = $this->getParams('hostname');
        $uname = $this->getParams('uname');
        $status = $this->getParams('status');
        $row = Env::find()->where(['hostname'=>$hostname, 'branch_name'=>$uname])->one();
        $row->status = $status;
        $ret = $row->save(false);//update
        if ($ret) {
            return $this->successData('success');
        }
        return $this->errorData('db failed');
    }
}