<?php

namespace app\common\exception;

use app\admin\exception\AdminJsonException;
use app\common\enums\ErrorCode;
use app\common\vo\ResultVo;
use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;

/**
 * 全局错误回调
 * Class GlobalHandle
 * @package app\common\exception
 */
class GlobalHandle extends Handle
{
    public function render(Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json(ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL));
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return response($e->getMessage(), $e->getStatusCode());
        }

        // 自定义的错误处理
        // admin 模块的异常
        if ($e instanceof AdminJsonException) {
            return json(ResultVo::error($e->getCode(), $e->getMessage()));
        }

        // 其他错误交给系统处理
        return parent::render($e);
    }

}
