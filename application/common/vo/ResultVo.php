<?php

namespace app\common\vo;

class ResultVo
{

    /**
     * 错误码
     * @var
     */
    public $errcode;

    /**
     * 错误信息
     * @var
     */
    public $errmsg;

    /**
     * 返回的data
     * @var
     */
    public $data;

    private function __construct($errcode, $errmsg, $data)
    {
        $this->errcode = $errcode;
        $this->errmsg = $errmsg;
        $this->data = $data;
    }

    /**
     * 请求成功的方法
     * @param $data
     * @return ResultVo
     */
    public static function success($data)
    {
        $instance = new self(null, null, $data);
        unset($instance->errcode);
        unset($instance->errmsg);
        return $instance;
    }

    /**
     * 请求错误
     * @param $errcode
     * @param $errmsg
     * @param $data
     * @return ResultVo
     */
    public static function error($errcode, $errmsg = null, $data = null)
    {
        if (is_array($errcode)) {
            $errmsg = isset($errcode['message']) && $errmsg == null ? $errcode['message'] : $errmsg;
            $errcode = isset($errcode['code']) ? $errcode['code'] : null;
        }
        $instance = new self($errcode, $errmsg, $data);
        if ($data == null) {
            unset($instance->data);
        }
        return $instance;
    }

}