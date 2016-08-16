<?php
/**
 * Created by PhpStorm.
 * User: xiemin02
 * Date: 2016/8/11
 * Time: 15:33
 */

namespace app\library\constant;


class ErrorInfo {
    //网络错误请求100开头
    const ERR_CURL_FAILD_ERROR = 100001;

    //命令行错误101开头，后接1表示sandagent业务
    const ERR_CONSOLE_SANDAGENT_PARAMS_FAILED = 101100;
    const ERR_CONSOLE_SANDAGENT_DIR_FAILED = 101101;
    const ERR_CONSOLE_SANDAGENT_METHOD_NOT_EXISTS = 101102;
    const ERR_CONSOLE_SANDAGENT_PATH_INVAILD = 101103;
    const ERR_CONSOLE_SANDAGENT_MODULE_INVAILD = 101104;

}
