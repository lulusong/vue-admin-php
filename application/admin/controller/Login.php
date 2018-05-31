<?php

namespace app\admin\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\model\Admin;
use app\common\model\AuthAccess;
use app\common\model\AuthRule;
use app\common\model\RoleAdmin;
use app\common\vo\ResultVo;

/**
 * 登录
 */

class Login extends Base
{
    /**
     * 获取用户信息
     * @return \think\response\Json
     * @throws JsonException
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
        $admin = Admin::where('username',$user_name)
            ->field('id,username,avatar,password,status')
            ->find();

        if (empty($admin) || Admin::getPass($pwd) != $admin->password){
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
            $role_ids = RoleAdmin::where('admin_id',$admin->id)->column('role_id');
            if ($role_ids){
                $auth_rule_ids = AuthAccess::where('role_id','in',$role_ids)
                    ->field(['auth_rule_id'])
                    ->select();
                foreach ($auth_rule_ids as $key=>$val){
                    $name = AuthRule::where('id',$val['auth_rule_id'])->value('name');
                    if ($name){
                        $authRules[] = $name;
                    }
                }
            }
        }
        $info['authRules'] = $authRules;
        //        $info['authRules'] = [
        //            'user_manage',
        //            'user_manage/admin',
        //            'admin/admin/index',
        //            'admin/role/index',
        //            'admin/authRule/index',
        //        ];
        // 保存用户信息
        $loginInfo = Admin::loginInfo($info['id'],$info);
        $res = [];
        $res['id'] = !empty($loginInfo['id']) ? intval($loginInfo['id']) : 0;
        $res['token'] = !empty($loginInfo['token']) ? $loginInfo['token'] : '';
        return json(ResultVo::success($res));
    }

    /**
     * 获取登录用户信息
     * @return \think\response\Json
     * @throws JsonException
     */
    public function userInfo()
    {
        $id = request()->header('X-Adminid');
        $token = request()->header('X-Token');
        if (!$id || !$token) {
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }
        $res = Admin::loginInfo($id, (string)$token);
        $res['id'] = !empty($res['id']) ? intval($res['id']) : 0;
        $res['avatar'] = !empty($res['avatar']) ? Admin::getAvatarUrl($res['avatar']) : '';
        // $res['roles'] = ['admin'];
        return json(ResultVo::success($res));
    }

    /**
     * 退出
     * @return string|\think\response\Json
     * @throws JsonException
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
        $loginInfo = Admin::loginInfo($id,(string)$token);
        if ($loginInfo == false){
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }

        Admin::loginOut($id);

        return json(ResultVo::success("SUCCESS"));

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
        $loginInfo = Admin::loginInfo($id,(string)$token);
        if ($loginInfo == false){
            throw new JsonException(ErrorCode::LOGIN_FAILED);
        }
        $old_password = request()->post('old_password');
        $new_password = request()->post('new_password');

        $admin_info = Admin::where('id',$id)->field('username,password')->find();
        if ($admin_info['password'] != Admin::getPass($old_password)){
            throw new JsonException(ErrorCode::USER_AUTH_FAIL, "原始密码错误");
        }

        if ($admin_info['password'] == Admin::getPass($new_password)){
            throw new JsonException(ErrorCode::USER_AUTH_FAIL, "密码未做修改");
        }

        $admin_info->password = Admin::getPass($new_password);
        if (!$admin_info->save()){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return json(ResultVo::success("SUCCESS"));

    }
}
