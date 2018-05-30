<?php

namespace app\admin\exception;

use think\Exception;

/**
 * admin 模块需要返回 Json 格式的错误异常
 * Class AdminException
 */
class AdminJsonException extends Exception
{

    public function __construct($errcode, $errmsg = null)
    {
        if (is_array($errcode)) {
            $errmsg = isset($errcode['message']) && $errmsg == null ? $errcode['message'] : $errmsg;
            $errcode = isset($errcode['code']) ? $errcode['code'] : null;
        }
        \Exception::__construct($errmsg, $errcode);
    }



}