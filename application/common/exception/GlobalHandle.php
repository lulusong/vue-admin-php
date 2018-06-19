<?php

namespace app\common\exception;

use app\common\enums\ErrorCode;
use app\common\vo\ResultVo;
use Exception;
use think\Container;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;

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
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        // 自定义的错误处理
        // admin 模块的异常
        if ($e instanceof JsonException) {
            return ResultVo::error($e->getCode(), $e->getMessage());
        }

        // 如果是正式环境，
        if (!Container::get('app')->isDebug()) {
            return $this->showException($e);
        }

        // 其他错误交给系统处理
        return parent::render($e);
    }

    /**
     * 为了在正式环境下显示具体的错误日志
     * @param Exception $exception
     * @return Response
     */
    private function showException(Exception $exception)
    {
        $showException = request()->get('showException');
        // 如果有显示错误的参数
        if ($showException != 'show') {
            $data = [];
            ob_start();
            extract($data);
            include __DIR__ . DIRECTORY_SEPARATOR . 'l404.html';
            // 获取并清空缓存
            $content  = ob_get_clean();
            $response = new Response($content, 'html');
            if (!isset($statusCode)) {
                $statusCode = 500;
            }
            $response->code($statusCode);
            return $response;
        }
        // 获取详细的错误信息
        $data = [
            'name'    => get_class($exception),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'message' => $this->getMessage($exception),
            'trace'   => $exception->getTrace(),
            'code'    => $this->getCode($exception),
            'source'  => $this->getSourceCode($exception),
            'datas'   => $this->getExtendData($exception),
            'tables'  => [
                'GET Data'              => $_GET,
                'POST Data'             => $_POST,
                'Files'                 => $_FILES,
                'Cookies'               => $_COOKIE,
                'Session'               => isset($_SESSION) ? $_SESSION : [],
                'Server/Request Data'   => $_SERVER,
                'Environment Variables' => $_ENV,
                'ThinkPHP Constants'    => $this->getGlobalConst(),
            ],
        ];
        //保留一层
        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        $data['echo'] = ob_get_clean();
        ob_start();
        extract($data);
        include __DIR__ . DIRECTORY_SEPARATOR . 'exception.html';
        // 获取并清空缓存
        $content  = ob_get_clean();
        $response = new Response($content, 'html');
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $response->header($exception->getHeaders());
        }
        if (!isset($statusCode)) {
            $statusCode = 500;
        }
        $response->code($statusCode);
        return $response;
    }

    /**
     * 获取常量列表
     * @return array 常量列表
     */
    private static function getGlobalConst()
    {
        $constants = get_defined_constants(true);
        return isset($constants['user']) ? $constants['user'] : [];
    }

}
