<?php
namespace app\index\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\model\Admin;
use app\common\vo\ResultVo;

class Index
{
    public function index()
    {

        throw new JsonException(ErrorCode::AUTH_FAILED,"1111");

    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
