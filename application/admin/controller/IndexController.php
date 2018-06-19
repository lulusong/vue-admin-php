<?php
namespace app\admin\controller;

use app\common\enums\ErrorCode;
use app\common\vo\ResultVo;

class IndexController
{
    public function index()
    {
        return ResultVo::error(ErrorCode::NOT_NETWORK);
    }
}
