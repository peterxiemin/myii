<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\modules\sandagent\commands;

use app\library\myconst\ErrorInfo;
use app\library\sandconsole\controllers\SandConsoleBaseController;
use app\library\util\http\Ral;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CallFuncController extends SandConsoleBaseController
{
    public $hostname;
    public $branchdir;
    public $uname;
    public $func;

    public function options()
    {
        return [
            'hostname',
            'branchdir',
            'uname',
            'func'
        ];
    }

    public function optionAliases()
    {
        return [
            'h' => 'hostname',
            'd' => 'branchdir',
            'u' => 'uname',
            'f' => 'func'
        ];
    }

    public function actionIndex()
    {
        try {
            //参数检查
            if (!$this->branchdir || !$this->uname || !$this->func) {
                $this->usage();
                return Controller::EXIT_CODE_NORMAL;
            }

            if (!file_exists($this->branchdir)) {
                throw new Exception('console sandagent dir error', ErrorInfo::ERR_CONSOLE_SANDAGENT_DIR_FAILED);
            }

            //适配参数
            $input = func_get_args();
            $params = $this->prepareParams($input);

            //调用callfunc执行命令
            $func = $params[3];
            if (method_exists($this, $func)) {
                $this->$func($params);
                return Controller::EXIT_CODE_NORMAL;
            }
            return Controller::EXIT_CODE_ERROR;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode);
        }
    }

    private function prepareParams($input)
    {
        $params = array();
        $params[0] = $this->hostname;
        $params[1] = $this->branchdir;
        $params[2] = $this->uname;
        $params[3] = $this->func;
        $args = $input;
        $i = 4;
        foreach ($args as $arg) {
            $params[$i] = $arg;
            $i++;
        }
        return $params;
    }

    private function checkpath($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        if ($uname == 'master' && is_dir($t10_dir)) {
            return true;
        }
        if (!is_dir($t10_dir . '_' . $uname)) {
            return false;
        }
        return true;
    }

    private function change_base_proc($params)
    {
        $dir = $params[1];
        $uname = $params[2];
        $servs = array('odp', 'mkt_caiwu', 'giftcard', 'hongbao', 'marketing', 'marketingMis', 'member', 'seserver', 't10_goodscenter', 'tp');
        foreach ($servs as $serv) {
            $params[0] = __FILE__;
            $params[1] = $dir . '/' . $serv;
            $params[2] = $uname;
            $params[3] = "change_fpm_num";
            $params[4] = 3;
            $this->change_fpm_num($params);
            if (!strcmp($serv, 'odp')) {
                $this->cancel_odp_https($params);
            }
            $this->change_ngx_worker($params);
        }

    }

    private function change_base_nmq($params)
    {
        $params[4] = 1;
        $this->cancel_nmq_restry($params);
        $this->change_nmq_thread($params);
        $this->replace_nmqconf();
    }

//因为需要升级gcc和安装swoole扩展，这部分暂时手动
    private function build_nmq_agent($params)
    {
        $timestamp = time();
        $cmd = "wget http://10.99.37.55:8333/static/swoole_agent.tar.gz -O /tmp/swoole_agent.tar.gz_$timestamp;";
        $cmd .= "tar -xzvf /tmp/swoole_agent.tar.gz_$timestamp -C /home/nuomi/;";
        $cmd .= "rm -f /tmp/swoole_agent.tar.gz_$timestamp;";
        $this->run_shell($cmd);
        $cmd = 'cd /home/nuomi/swoole_agent/odp/app/nmq_agent/;';
        $cmd .= '/home/nuomi/swoole_agent/odp/php/bin/php /home/nuomi/swoole_agent/odp/app/nmq_agent/server.php > server.log &';
        $this->run_shell($cmd);
        $parasm = array();
        $params[4] = 8118;
        $params[5] = 8117;
        $this->change_nmq_send_port($params);
    }

    private function copy_t10_goodscenter($params)
    {
        $host = $params[0];
        $t10_dir = $params[1];
        $uname = $params[2];
        if (is_dir("$t10_dir/t10_goodscenter_$uname")) {
            throw new Exception('copy_t10_goodscenter dir is not vaild', ERR_CONSOLE_SANDAGENT_DIR_FAILED);
        }
        $cmd = "cd $t10_dir;";
        $cmd .= "cp -r t10_goodscenter t10_goodscenter_$uname";
        $this->run_shell($cmd);
        $params[1] = "$t10_dir/t10_goodscenter";
        //取消403
        $this->cancel_403($params);
        //分配nginx端口,先写死
        $new_params = $params;
        $new_params[4] = $params[5];
        $new_params[5] = $params[7];//写死

        $this->change_ngx_port($new_params);
        //启动php-fpm
        $cmd = "cd $t10_dir/t10_goodscenter_$uname;";
        $cmd .= "./php/sbin/php-fpm restart";
        $this->run_shell($cmd);
        //更新odp配置
        $this->replace_odpconf($params);
        //做闭环处理
        //$new_params = $params;
        //$params[4] = '10.46.132.177';
        //$params[5] = '8118';
        //$params[6] = '10.101.44.195';
        //$params[7] = '8119';
        $this->conf_cycle($params);
        //拉去receiver
        $this->pull_receiver($params);
        //更新toplib
        sleep(1.5);
        $this->update_toplib($params);
        //增加nmq转发
        $this->add_sandbox_port($params);
        //调用console接口改变分支部署状态
        $params = array(
            'hostname' => $this->hostname,
            'uname'    => $this->uname,
            'status'   => 1,
        );
        $ret = Ral::requestUrl($this->getIndexUrl(). '?r=api-agent/update-status', $params);
        if ($ret['err']) {
            Yii::warning(__CLASS__ . ' ' . __FUNCTION__ . 'request url errinfo : ' . $ret['msg']);
        }
        Yii::trace(__CLASS__ . '' . __FUNCTION__ . 'request ret : ' . $ret['data']);
    }

    private function rmcopy_t10_goodscenter($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = rtrim($t10_dir, '/');
        $t10_dir = $t10_dir . "_$uname";
        $cmd = "cd $t10_dir;";
        $cmd .= "./webserver/loadnginx.sh stop;";
        $cmd .= "./php/sbin/php-fpm stop;";
        $cmd .= "rm -rf $t10_dir";
        $this->run_shell($cmd);
    }

    private function mkdir_logpath($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . '_' . $uname . '/';
        $path = $t10_dir;
        $out = array();
        $mkdir = array(
            'goodsdata',
            'goodsinfo',
            'paynow',
            'storecard',
            't10misplatform',
            'tradecenter',
        );
        foreach ($mkdir as $dir) {
            $cmd = 'mkdir ' . $path . '/log/' . $dir;
            $this->run_shell($cmd);
        }
    }

    private function cancel_403($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . '_' . $uname . '/';
        $dir = $t10_dir . '/webserver/conf/vhost';
        $file = 'php.conf';
        $cmd = "sed -i '35,37s/^/#/' $dir/$file";
        $this->run_shell($cmd);
        $cmd = "cd $t10_dir/webserver/;sh loadnginx.sh reload";
        $this->run_shell($cmd);
    }

    private function change_ngx_port($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . '_' . $uname . '/';
        $dir = $t10_dir . '/webserver/conf/vhost';
        $file = 'php.conf';
        $pre_port = $params[4];
        $after_port = $params[5];
        $cmd = "sed -i 's/listen              $pre_port/listen              $after_port/' $dir/$file";
        $this->run_shell($cmd);
        $cmd = 'cd ' . $t10_dir . '/webserver/;sh loadnginx.sh restart';
        $this->run_shell($cmd);
    }

    private function run_shell($cmd)
    {
        $out = array();
        $ret = exec($cmd, $out);
        print_r($out);
    }

    private function conf_cycle($params)
    {
        if (!$this->checkpath($params)) {
            throw new Exception('path is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_PATH_INVAILD);
        }
        $t10_dir = $params[1];
        $uname = $params[2];

        $t10_dir = $t10_dir . '_' . $uname;
        $dir = $t10_dir . '/conf/app/tradecenter';
        $file = 'pay.conf';
        $pre_host = $params[4];
        $pre_port = $params[5];
        $after_host = $params[6];
        $after_port = $params[7];
        $cmd = "sed -i 's/$pre_host:$pre_port/$after_host:$after_port/g' $dir/$file"; //new style str concat var
        $this->run_shell($cmd);

        //conf/ral/services/paynow_tradecenter.conf
        $dir = $t10_dir . '/conf/ral/services';
        $file = 'paynow_tradecenter.conf';
        $cmd = "sed -i 's/DefaultPort : $pre_port/DefaultPort : $after_port/' $dir/$file";
        $this->run_shell($cmd);

        //conf/ral/services/tradecenter.conf
        $file = 'tradecenter.conf';
        $cmd = "sed -i 's/DefaultPort : $pre_port/DefaultPort : $after_port/' $dir/$file";
        $this->run_shell($cmd);

        //conf/ral/services/paynow.conf
        $file = 'paynow.conf';
        $cmd = "sed -i 's/DefaultPort : $pre_port/DefaultPort : $after_port/' $dir/$file";
        $this->run_shell($cmd);
    }

    private function replace_odpconf($params)
    {
        if (!$this->checkpath($params)) {
            throw new Exception('path is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_PATH_INVAILD);
        }
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . '_' . $uname;
        $timestamp = time();
        $cmd = 'cd ' . $t10_dir . ';';
        $cmd .= "tar -czvf conf_$timestamp.tar.gz conf/;";
        $cmd .= "rm -rf ./conf;";
        $cmd .= "git clone http://gitlab.baidu.com/t10sandbox/sandodpconf.git /tmp/sandodpconf_$timestamp;";
        $cmd .= "mv /tmp/sandodpconf_$timestamp/conf ./;";
        $cmd .= "rm -rf /tmp/sandodpconf_$timestamp";
        $this->run_shell($cmd);
    }


    private function pull_receiver($params)
    {
        if (!$this->checkpath($params)) {
            throw new Exception('path is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_PATH_INVAILD);
        }
        $timestamp = time();
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . '_' . $uname;
        $cmd = 'cd ' . $t10_dir . ';';
        $cmd .= "git clone http://gitlab.baidu.com/t10sandbox/sandodpconf.git /tmp/sandodpconf_$timestamp;";
        $cmd .= "mv /tmp/sandodpconf_$timestamp/extrafile/receiver.php ./webroot/static/";
        $this->run_shell($cmd);
    }

    private function change_fpm_num($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . (strcmp($uname, 'master') === 0 ? '' : '_' . $uname);
        $proc_num = $params[4];
        $cmd = 'cd ' . $t10_dir . ';';
        $cmd .= "sed -ir 's/<value name=\"max_children\">.*</<value name=\"max_children\">$proc_num</g' php/etc/php-fpm.conf;";
        $cmd .= 'php/sbin/php-fpm restart';
        $this->run_shell($cmd);
    }

    private function change_ngx_worker($params)
    {
        $t10_dir = $params[1];
        $uname = $params[2];
        $t10_dir = $t10_dir . (strcmp($uname, 'master') === 0 ? '' : '_' . $uname);
        $cmd = 'cd ' . $t10_dir . ';';
        $cmd .= "sed -ir 's/worker_processes  .*;/worker_processes  1;/g' webserver/conf/nginx.conf;";
        $cmd .= 'sh webserver/loadnginx.sh restart';
        $this->run_shell($cmd);
    }

    private function replace_nmqconf()
    {
        $nmqs = array('nmq_t10');
        $timestamp = time();
        foreach ($nmqs as $nmq) {
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/;";
            $cmd .= "tar -czvf conf_$timestamp.tar.gz conf;";
            $cmd .= "rm -rf ./conf;";
            $cmd .= "git clone http://gitlab.baidu.com/t10sandbox/sandodpconf.git /tmp/sandodpconf_$timestamp;";
            $cmd .= "mv /tmp/sandodpconf_$timestamp/nmq_conf ./conf;";
            $cmd .= "rm -rf /tmp/sandodpconf_$timestamp";
            $this->run_shell($cmd);
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/; ./bin/pusher_control restart";
            $this->run_shell($cmd);
        }
    }

    private function change_nmq_post_fmt()
    {
        $nmqs = array('nmq_t10');
        $timestamp = time();
        $method = 'add';
        foreach ($nmqs as $nmq) {
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/conf;";
            if ($method == 'add') {
                $cmd .= "sed -i '/http_header/s/$/\\r\\nContent-Type: application\/x-www-form-urlencoded/' module_*.conf;";
            } else {
                $cmd .= "sed -i '/http_header/s/\\r\\nContent-Type: application\/x-www-form-urlencoded//' module_*.conf";
            }
            $this->run_shell($cmd);
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/; ./bin/pusher_control restart";
            $this->run_shell($cmd);
        }
    }

    private function cancel_nmq_restry($params)
    {
        $num = $params[4];
        $nmqs = array('nmq_t10');
        foreach ($nmqs as $nmq) {
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/conf;";
            $cmd .= "sed -i 's/sending_retry_time.*/sending_retry_time : $num/g' module*.conf;";
            $cmd .= "sed -i 's/max_retry_times.*/max_retry_times : $num/g' module*.conf";
            $this->run_shell($cmd);
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher;./bin/pusher_control restart";
            $this->run_shell($cmd);
        }
    }

    private function change_nmq_thread($params)
    {
        $num = $params[4];
        $nmqs = array('nmq_member', 'nmq_product', 'nmq_t10');
        foreach ($nmqs as $nmq) {
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/conf;";
            $cmd .= "sed -i 's/sending_window_size.*/sending_window_size : $num/g' *.conf;";
            $cmd .= "sed -i 's/sending_thread_num.*/sending_thread_num: $num/g' *.conf";
            $this->run_shell($cmd);
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher;./bin/pusher_control restart";
            $this->run_shell($cmd);
        }
    }

    private function change_nmq_send_port($params)
    {
        $pre_port = $params[4];
        $after_port = $params[5];
        $nmqs = array('nmq_t10');
        foreach ($nmqs as $nmq) {
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/conf;";
            $cmd .= "sed -i 's/Port : $pre_port/Port : $after_port/g' machine_*.conf;";
            //修改下游post发送格式
            $this->run_shell($cmd);
            $cmd = "cd /home/nuomi/nmq/$nmq/pusher/;";
            $cmd .= "./bin/pusher_control restart";
            $this->run_shell($cmd);
        }
    }

    private function update_code($params)
    {
        if (!$this->checkpath($params)) {
            throw new Exception('path is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_PATH_INVAILD);
        }
        $t10_dir = $params[1];
        $uname = $params[2];
        if ($uname != 'master') {
            $t10_dir = $t10_dir . '_' . $uname;
        }
        $module = $params[4];
        $svn_path = $params[5];
        $invaild_module = array('paynow', 'tradecenter');
        if (!in_array($module, $invaild_module)) {
            throw new Exception('modudle[' . $module . '] is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_MODULE_INVAILD);
        }
        if (!is_dir($t10_dir . '/app/' . $module)) {
            throw new Exception($t10_dir . '/app/' . $module . ' is invaild dir', ErrorInfo::ERR_CONSOLE_SANDAGENT_DIR_FAILED);
        }
        $cmd = 'cd ' . $t10_dir . ';';
        $cmd .= 'tar -czvf app/' . $module . '_' . time() . '.tar.gz app/' . $module . ';';
        $cmd .= "rm -rf app/$module;";
        $cmd .= "svn co $svn_path app/$module";
        $this->run_shell($cmd);
    }

    private function add_sandbox_port($params)
    {
        if (!$this->checkpath($params)) {
            throw new Exception('path is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_PATH_INVAILD);
        }
        $t10_dir = $params[1];
        $uname = $params[2];
        $cmd = 'cd ' . $t10_dir . '_' . $uname . ';';
        $cmd .= "sed -i '23 a\$arrParam[\"sandbox_port\"] = isset(\$_SERVER[\"SERVER_PORT\"]) && \$_SERVER[\"SERVER_PORT\"] >= 8118 ? \$_SERVER[\"SERVER_PORT\"] : 8118;'  ./php/phplib/toplib/base/RalBase.php";
        $this->run_shell($cmd);
    }

    private function update_toplib($params)
    {
        if (!$this->checkpath($params)) {
            throw new Exception('path is invaild', ErrorInfo::ERR_CONSOLE_SANDAGENT_PATH_INVAILD);
        }
        $t10_dir = $params[1];
        $uname = $params[2];
        $timestamp = time();
        $cmd = 'cd ' . $t10_dir . '_' . $uname . '/php/phplib/;';
        $cmd .= "tar -czvf toplib_$timestamp.tar.gz toplib/;";
        $cmd .= "rm -rf ./toplib;";
        $cmd .= "git clone http://gitlab.baidu.com/t10sandbox/sandodpconf.git /tmp/sandodpconf_$timestamp;";
        $cmd .= "mv /tmp/sandodpconf_$timestamp/toplib ./;";
        $cmd .= "rm -rf /tmp/sandodpconf_$timestamp";
        $this->run_shell($cmd);
    }

    private function cancel_odp_https($params)
    {
        $t10_dir = $params[1];
        $cmd = "cd $t10_dir;";
        $cmd .= "sed -i '121,137s/^/#/' webserver/conf/nginx.conf";
        $this->run_shell($cmd);
    }


    private function usage()
    {
        print_r("############################################################\n");
        print_r("######################usage#################################\n");
        print_r("############################################################\n");
        print_r("update_code:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=update_code paynow 	https://svn.baidu.com/app/search/baidunuomi/branches/t10/paynow/paynow_1-0-117_BRANCH\n");
        print_r("add_sandbox_port:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=add_sandbox_port \n");
        print_r("update_toplib:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=update_toplib \n");
        print_r("change_nmq_send_port:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=change_nmq_send_port 8118 8117 \n");
        print_r("change_nmq_thread:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=change_nmq_thread 1 \n");
        print_r("cancel_nmq_restry:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=cancel_nmq_restry 1 \n");
        print_r("change_nmq_post_fmt:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=change_nmq_post_fmt \n");
        print_r("replace_nmqconf:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=replace_nmqconf \n");
        print_r("change_ngx_worker:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=change_ngx_worker \n");
        print_r("change_fpm_num:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=change_fpm_num \n");
        print_r("pull_receiver:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=pull_receiver\n");
        print_r("replace_odpconf:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=replace_odpconf\n");
        print_r("conf_cycle:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=conf_cycle 10.46.132.177 8118 10.99.207.84 8119\n");
        print_r("change_ngx_port:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=change_ngx_port 8118 8119\n");
        print_r("cancel_403:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=cancel_403\n");
        print_r("mkdir_logpath:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=mkdir_logpath\n");
        print_r("cancel_odp_https:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/odp -u=master -f=cancel_odp_https\n");
        print_r("change_base_proc:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c -u=master -f=change_base_proc\n");
        print_r("change_base_nmq:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c -u=master -f=change_base_nmq\n");
        print_r("build_nmq_agent:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/ -u=master -f=build_nmq_agent\n");
        print_r("copy_t10_goodscenter:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c -u=xiemin02 -f=copy_t10_goodscenter 10.46.132.177 8118 10.99.207.84 8119\n");
        print_r("rmcopy_t10_goodscenter:\n");
        print_r("php yii sandagent/call-func -d=/home/nuomi/workspace_c/t10_goodscenter -u=xiemin02 -f=rmcopy_t10_goodscenter\n");
        print_r("############################################################\n");
        print_r("############################################################\n");
        print_r("############################################################\n");
    }
}
