<?php

namespace app\common\vo;

class ResultVo
{

    /**
     * 错误码
     * @var
     */
    public $code;

    /**
     * 错误信息
     * @var
     */
    public $message;

    /**
     * 返回的data
     * @var
     */
    public $data;

    private function __construct($code, $message, $data)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * 请求成功的方法
     * @param $data
     * @return \think\response\Json
     */
    public static function success($data)
    {
        return json($data);
    }

    /**
     * 请求错误
     * @param $code
     * @param null $message
     * @param string $data
     * @return \think\response\Json
     */
    public static function error($code, $message = null, $data = '')
    {
        if (is_array($code)) {
            $message = isset($code['message']) && $message == null ? $code['message'] : $message;
            $code = isset($code['code']) ? $code['code'] : null;
        }
        $instance = new self($code, $message, $data);
        return json($instance);
    }

}