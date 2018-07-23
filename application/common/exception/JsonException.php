<?php

namespace app\common\exception;

use think\Exception;


/**
 * 返回 Json 格式的错误异常
 */
class JsonException extends Exception
{

    public function __construct($code, $message = "")
    {
        if (is_array($code)) {
            $message = isset($code['message']) && empty($message) ? $code['message'] : $message;
            $code = isset($code['code']) ? $code['code'] : 0;
        }
        \Exception::__construct($message, $code);
    }

}