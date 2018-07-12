<?php

namespace app\admin\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\model\auth\AuthAdmin;
use app\common\model\auth\AuthPermission;
use app\common\model\auth\AuthPermissionRule;
use app\common\model\auth\AuthRoleAdmin;
use app\common\vo\ResultVo;

/**
 * 登录
 */

class LoginController extends Base
{
    /**
     * 获取用户信息
     */
    public function index()
    {

        if (!request()->isPost()){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }

        $user_name = request()->post('userName');
        $pwd = request()->post('pwd');

        if (!$user_name || !$pwd){
            throw new JsonException(ErrorCode::VALIDATION_FAILED, "username 不能为空。 password 不能为空。");
        }
        $admin = AuthAdmin::where('username',$user_name)
            ->field('id,username,avatar,password,status')
            ->find();

        if (empty($admin) || AuthAdmin::getPass($pwd) != $admin->password){
            throw new JsonException(ErrorCode::USER_AUTH_FAIL);
        }
        if ($admin->status != 1){
            throw new JsonException(ErrorCode::USER_NOT_PERMISSION);
        }

        $info = $admin->toArray();

        unset($info['password']);

        // 权限信息
        $authRules = [];
        if ($user_name == 'admin'){
            $authRules = ['admin'];
        }else{
            $role_ids = AuthRoleAdmin::where('admin_id',$admin->id)->column('role_id');
            if ($role_ids){
                $permission_rule_ids = AuthPermission::where('role_id','in',$role_ids)
                    ->field(['permission_rule_id'])
                    ->select();
                foreach ($permission_rule_ids as $key=>$val){
                    $name = AuthPermissionRule::where('id',$val['permission_rule_id'])->value('name');
                    if ($name){
                        $authRules[] = $name;
                    }
                }
            }
        }
        $info['authRules'] = $authRules;
       // $info['authRules'] = [
       //     'user_manage',
       //     'user_manage/admin_manage',
       //     'admin/admin/index',
       //     'admin/role/index',
       //     'admin/auth_admin/index',
       // ];
        // 保存用户信息
        $loginInfo = AuthAdmin::loginInfo($info['id'],$info);
        $admin->last_login_ip = request()->ip();
        $admin->last_login_time = time();
        $admin->save();
        $res = [];
        $res['id'] = !empty($loginInfo['id']) ? intval($loginInfo['id']) : 0;
        $res['token'] = !empty($loginInfo['token']) ? $loginInfo['token'] : '';
        return ResultVo::success($res);
    }

    /**
     * 获取登录用户信息
     */
    public function userInfo()
    {
        $id = request()->header('X-Adminid');
        $token = request()->header('X-Token');
        if (!$id || !$token) {
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }
        $res = AuthAdmin::loginInfo($id, (string)$token);
        $res['id'] = !empty($res['id']) ? intval($res['id']) : 0;
        $res['avatar'] = !empty($res['avatar']) ? AuthAdmin::getAvatarUrl($res['avatar']) : '';
        // $res['roles'] = ['admin'];
        return ResultVo::success($res);
    }

    /**
     * 退出
     */
    public function out()
    {
        if (!request()->isPost()){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }

        $id = request()->header('X-Adminid');
        $token = request()->header('X-Token');
        if (!$id || !$token) {
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }
        $loginInfo = AuthAdmin::loginInfo($id,(string)$token);
        if ($loginInfo == false){
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }

        AuthAdmin::loginOut($id);

        return ResultVo::success("SUCCESS");

    }


    /**
     * 修改密码
     */
    public function password(){
        if (!request()->isPost()){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $id = request()->header('X-Adminid');
        $token = request()->header('X-Token');
        if (!$id || !$token) {
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }
        $loginInfo = AuthAdmin::loginInfo($id,(string)$token);
        if ($loginInfo == false){
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }
        $old_password = request()->post('old_password');
        $new_password = request()->post('new_password');

        $admin_info = AuthAdmin::where('id',$id)->field('username,password')->find();
        if ($admin_info['password'] != AuthAdmin::getPass($old_password)){
            throw new JsonException(ErrorCode::USER_AUTH_FAIL, "原始密码错误");
        }

        if ($admin_info['password'] == AuthAdmin::getPass($new_password)){
            throw new JsonException(ErrorCode::USER_AUTH_FAIL, "密码未做修改");
        }

        $admin_info->password = AuthAdmin::getPass($new_password);
        if (!$admin_info->save()){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return ResultVo::success("SUCCESS");

    }
}
