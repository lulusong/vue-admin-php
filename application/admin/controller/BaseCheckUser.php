<?php

namespace app\admin\controller;

use app\common\model\Admin;
use think\facade\Hook;

/**
 * 用户基础控制器
 */

class BaseCheckUser extends Base
{

    public $adminInfo = '';

    public function initialize()
    {
        parent::initialize();

        // 监听登录的钩子
        $params = [];
        Hook::listen('app_init',$params);

        $id = request()->header('X-Adminid');
        $token = request()->header('X-Token');
        $loginInfo = Admin::loginInfo($id, (string)$token);
        $this->adminInfo = $loginInfo;


    }

}
