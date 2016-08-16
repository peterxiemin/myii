<?php

namespace app\modules\sandagent\controllers;
use Yii;
use yii\base\Exception;
use app\library\sandconsole\controllers\SandConsoleBaseController;
/**
 * Class AgentController
 * @package app\modules\sandagent\controllers
 */
class AgentController extends SandConsoleBaseController
{
	/**
	 * Renders the index view for the module
	 * @return string
	 */


	const PARAM_MODE_GET = 1;
	const PARAM_MODE_POST = 2;
	const PARAM_MODE_BOTH = 3;
	const ERR_PARAM_EMPTY = 1;
	const ERR_ACTION_NOT_FOUND = 2;
	const ERR_FILE_NOT_FOUND = 3;
	const ERR_NOT_A_FILE = 4;
	const ERR_FILE_EXISTED = 5;
	const ERR_PARENT_DIR_NOT_EXISTS = 6;
	const ERR_UPDATECODE_FAILED = 7;
	const ERR_INVAILD_UNAME = 8;
	const ERR_INVAILD_PATH = 9;
	const ERR_PATH_EXISTS = 10;
	const ERR_COPY_FAILED = 11;
	//部署脚本路径
	const SCRIPT_PATH = '/home/nuomi/yii/webroot/sandconsole';

	public function actionIndex()
	{
		try{
			$action = $this->getParam('action');
			$this->handleAction($action);
		}catch (Exception $e){
			$this->error($e->getCode(), $e->getMessage());
		}
	}

	private function lsAction(){
		$path = $this->getParam('path');
		$this->checkFile($path);
		$ret = array();
		if(is_dir($path)){
			$dir_path = substr($path, strlen($path) - 1, 1) == DIRECTORY_SEPARATOR ? $path : $path . DIRECTORY_SEPARATOR;
			$files = scandir($path);

			foreach($files as $i => $fileName){
				if($fileName == '.' || $fileName == '..'){
					continue;
				}
				$filePath = $dir_path . $fileName;
				$isDir = is_dir($filePath);
				$ext = '';
				if(!$isDir){
					$fileInfo = pathinfo($filePath);
					$ext = $fileInfo['extension'];
				}

				$ret[] = array(
						'name' => $fileName,
						'is_dir' => $isDir,
						'path' => $filePath,
						'dir' => dirname($filePath),
						'ext' => $ext,
					      );
			}
		}else{
			$fileInfo = pathinfo($path);
			$ret[] = array(
					'name' => basename($path),
					'is_dir' => false,
					'path' => $path,
					'dir' => dirname($path),
					'ext' => $fileInfo['extension'],
				      );

		}

		return $ret;

	}

	/**
	 * 读取文件内容
	 * @return array
	 * @throws Exception
	 */
	private function readFileAction(){
		$path = $this->getParam('path');
		$this->checkFile($path);
		if(is_file($path)){
			return file_get_contents($path);
		}else{
			$this->throwException('not a file', ERR_NOT_A_FILE);
		}
	}

	/**
	 * 新建文件
	 */
	private function newFileAction(){
		$path = $this->getParam('path');
		$this->checkFile($path, true);
		if(!file_exists(dirname($path))){
			$this->throwException('parent directory not exists', ERR_PARENT_DIR_NOT_EXISTS);
		}
		$fp = fopen($path, 'w');
		fclose($fp);
		return !empty($fp);
	}

	/**
	 * 写入文件
	 */
	private function writeFileAction(){
		$path = $this->getParam('path');
		if(!file_exists(dirname($path))){
			$this->throwException('parent directory not exists', ERR_PARENT_DIR_NOT_EXISTS);
		}
		$content = $this->getParam('content', false);
		return file_put_contents($path, $content) !== false;
	}
	/*************************************************************************************************************/


	private function handleAction($action){
		$actionHandler = $action . 'Action';
		if(method_exists($this, $actionHandler)){
			$this->success($this->$actionHandler());
		}else{
			$this->error(ERR_ACTION_NOT_FOUND, 'action not found');
		}
	}

	private function checkFile($path, $notExists=false){
		$exists = file_exists($path);
		if(!$exists && !$notExists){
			$this->throwException('invalid path', ERR_FILE_NOT_FOUND);
		}
		if($exists && $notExists){
			$this->throwException('file has been existed', ERR_FILE_EXISTED);
		}
	}

	private function cloneAction() {
		$uname = $this->getParam('uname');
		$path = $this->getParam('path');
		if (!is_dir($path)) {
			$this->throwException($path . ' not exists', ERR_INVAILD_PATH);
		}
		$path =  str_replace("t10_goodscenter", '', $path);
		$path = rtrim($path, '/');
		$php_bin = '/home/nuomi/yii/php/bin/php';
		$port = $this->getParam('port');
		$cmd = 'cd ' . self::SCRIPT_PATH . ';';
		if ($uname == 'master') {
			$this->throwException('invaild uname', ERR_INVAILD_UNAME);
		}
		$cmd .= "$php_bin yii sandagent/call-func -d=$path -u=$uname -f=copy_t10_goodscenter 10.46.132.177 8118 10.99.207.84 $port > /dev/null 2>&1 &";
		//耗时比较大做异步处理
		$ret = $this->runShell($cmd);
		return 'success';
	}

	private function delcopyAction() {
		$uname = $this->getParam('uname');
		if ($uname == 'master') {
			$this->throwException('invaild uname', ERR_INVAILD_UNAME);
		}
		$path = $this->getParam('path');
		$php_bin = '/home/nuomi/yii/php/bin/php';
		$cmd = 'cd ' . self::SCRIPT_PATH . ';';
		$cmd .= "$php_bin yii sandagent/call-func -d=$path -u=$uname -f=rmcopy_t10_goodscenter";
		$ret = $this->runShell($cmd, 'done');
		if ($ret) {
			return 'success';
		}
		$this->throwException('delcopy failed', ERR_COPY_FAILED);

	}
	private function updateCodeAction() {
		$uname = $this->getParam('uname');
		$path = $this->getParam('path');
		$app = $this->getParam('app');
		$svn = $this->getParam('svn');
		$php_bin = '/home/nuomi/yii/php/bin/php';
		//adapter callfunc args
		$path = str_replace("_$uname", '', $path);
		$path = rtrim($path, '/');
		if (!is_dir($path)) {
			$this->throwException('invaild path', ERR_INVAILD_PATH);
		}
		$cmd = 'cd ' . self::SCRIPT_PATH . ';';
		$cmd .= "$php_bin yii sandagent/call-func -d=$path -u=$uname -f=update_code $app $svn";
		$ret = $this->runShell($cmd, 'Checked out');
		if ($ret) {
			return 'success';
		}
		$this->throwException('update code failed', ERR_UPDATECODE_FAILED);
	}

	private function runShell($cmd, $res = '') {
		$out = array();
		$ret = exec($cmd, $out);
		if (!empty($res)) {
			foreach ($out as $str) {
				if (stripos($str, $res) !== false) {
					return true;
				}
			}
			return false;
		}
		return true;
	}

	private function getParam($param, $checkEmpty=true, $mode=PARAM_MODE_BOTH){
		$paramValue = null;
		if($mode == PARAM_MODE_GET){
			$paramValue = $_POST[$param];
		}else if($mode == PARAM_MODE_POST){
			$paramValue = $_GET[$param];
		}else{
			$paramValue = $_GET[$param];
			if($paramValue === null){
				$paramValue = $_POST[$param];
			}
		}
		if($checkEmpty && empty($paramValue) && $paramValue !== '0'){
			$this->throwException('param \'' . $param . '\' not found', ERR_PARAM_EMPTY);
		}
		return $paramValue;
	}

	private function throwException($msg, $code){
		throw new Exception($msg);
	}

	private function success($data){
		$this->doResponse(array(
					'errno' => 0,
					'data' => $data,
				       ));
	}

	private function error($errno, $msg){
		$this->doResponse(array(
					'errno' => $errno,
					'msg' => $msg,
				       ));
	}

	private function doResponse($out){
		if(is_array($out) || is_object($out)){
			echo json_encode($out);
		}else{
			echo $out;
		}
	}
}
