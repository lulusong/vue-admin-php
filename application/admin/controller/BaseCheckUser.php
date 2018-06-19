<?php

namespace app\admin\controller;

use app\common\model\AuthAdmin;
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
        $login_info = AuthAdmin::loginInfo($id, (string)$token);
        $this->adminInfo = $login_info;


    }

}
