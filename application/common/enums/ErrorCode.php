<?php
// +----------------------------------------------------------------------
// | ThinkPHP 5 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 .
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 黎明晓 <lmxdawn@gmail.com>
// +----------------------------------------------------------------------

namespace app\common\enums;

/**
 * 后台系统错误码
 * Class ErrorCode
 * @package app\common\model
 */
class ErrorCode
{

    // +----------------------------------------------------------------------
    // | 系统级错误码
    // +----------------------------------------------------------------------
    const NOT_NETWORK = [ 'code' => 10001, 'message' => '网络繁忙'];

    // +----------------------------------------------------------------------
    // | 服务级错误码
    // +----------------------------------------------------------------------
    const HTTP_METHOD_NOT_ALLOWED = [ 'code' => 20001, 'message' => '网络请求不予许'];
    const VALIDATION_FAILED = [ 'code' => 20002, 'message' => '身份验证失败'];
    const USER_AUTH_FAIL = [ 'code' => 20003, 'message' => '用户名或者密码错误'];
    const USER_NOT_PERMISSION = [ 'code' => 20004, 'message' => '当前没有权限登录'];
    const AUTH_FAILED = [ 'code' => 20005, 'message' => '权限验证失败'];
    const LOGIN_FAILED = [ 'code' => 20006, 'message' => '登录失效'];
    const DATA_CHANGE = [ 'code' => 20007, 'message' => '数据没有任何更改'];
    const DATA_REPEAT = [ 'code' => 20008, 'message' => '数据重复'];
    const DATA_NOT = [ 'code' => 20009, 'message' => '数据不存在'];
    const DATA_VALIDATE_FAIL = [ 'code' => 20010, 'message' => '数据验证失败'];

    // 管理员相关

}
