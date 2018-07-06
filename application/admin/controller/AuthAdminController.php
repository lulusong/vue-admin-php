<?php

namespace app\admin\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\model\auth\AuthAdmin;
use app\common\model\auth\AuthRole;
use app\common\model\auth\AuthRoleAdmin;
use app\common\vo\ResultVo;

/**
 * 管理员相关
 */
class AuthAdminController extends BaseCheckUser
{

    /**
     * 列表
     */
    public function index()
    {

        $where = [];
        $order = 'id DESC';
        $status = request()->get('status', '');
        if ($status !== ''){
            $where[] = ['status','=',intval($status)];
            $order = '';
        }
        $username = request()->get('username', '');
        if (!empty($username)){
            $where[] = ['username','like',$username . '%'];
            $order = '';
        }
        $role_id = request()->get('role_id/id', '');
        if ($role_id !== ''){
            $admin_ids = AuthRoleAdmin::where('role_id',$role_id)->column('admin_id');
            $where[] = ['id','in',$admin_ids];
            $order = '';
        }
        $limit = request()->get('limit/d', 20);
        //分页配置
        $paginate = [
            'type' => 'bootstrap',
            'var_page' => 'page',
            'list_rows' => ($limit <= 0 || $limit > 20) ? 20 : $limit,
        ];
        $lists = AuthAdmin::where($where)
            ->field('id,username,avatar,tel,email,status,last_login_ip,last_login_time,create_time')
            ->order($order)
            ->paginate($paginate);

        foreach ($lists as $k => $v) {
            $v['avatar'] = AuthAdmin::getAvatarUrl($v['avatar']);
            $roles = AuthRoleAdmin::where('admin_id',$v['id'])->field('role_id')->select();
            $temp_roles = [];
            if ($roles){
                $temp_roles = $roles->toArray();
                $temp_roles = array_column($temp_roles,'role_id');
            }
            $v['roles'] = $temp_roles;
            $lists[$k] = $v;
        }

        $role_list = AuthRole::where('status',1)
            ->field('id,name')
            ->order('id ASC')
            ->select();

        $res['admin_list'] = $lists;
        $res['role_list'] = $role_list;

        return ResultVo::success($res);

    }

    /**
     * 添加
     */
    public function save(){
        $data = request()->post();
        if (empty($data['username']) || empty($data['password'])){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $username = $data['username'];
        // 模型
        $info = AuthAdmin::where('username',$username)
            ->field('username')
            ->find();
        if ($info){
            throw new JsonException(ErrorCode::DATA_REPEAT);
        }

        $status = isset($data['status']) ? $data['status'] : 0;
        $auth_admin = new AuthAdmin();
        $auth_admin->username = $username;
        $auth_admin->password = AuthAdmin::getPass($data['password']);
        $auth_admin->status = $status;
        $auth_admin->create_time = time();
        $result = $auth_admin->save();

        if (!$result){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        $roles = (isset($data['roles']) && is_array($data['roles'])) ? $data['roles'] : [];

        //$adminInfo = $this->adminInfo; // 登录用户信息
        $admin_id = $auth_admin->id;
        if ($roles){
            $temp = [];
            foreach ($roles as $key => $value){
                $temp[$key]['role_id'] = $value;
                $temp[$key]['admin_id'] = $admin_id;
            }
            //添加用户的角色
            $auth_role_admin = new AuthRoleAdmin();
            $auth_role_admin->saveAll($temp);
        }

        $auth_admin['password'] = '';
        $auth_admin['roles'] = $roles;

        return ResultVo::success($auth_admin);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = request()->post();
        if (empty($data['id']) || empty($data['username'])){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $id = $data['id'];
        $username = strip_tags($data['username']);
        // 模型
        $auth_admin = AuthAdmin::where('id',$id)
            ->field('id,username')
            ->find();
        if (!$auth_admin){
            throw new JsonException(ErrorCode::DATA_NOT, "管理员不存在");
        }
        $login_info = $this->adminInfo;
        $login_user_name = isset($login_info['username']) ? $login_info['username'] : '';
        // 如果是超级管理员，判断当前登录用户是否匹配
        if ($auth_admin->username == 'admin' && $login_user_name != $auth_admin->username){
            throw new JsonException(ErrorCode::DATA_NOT, "最高权限用户，无权修改");
        }

        $info = AuthAdmin::where('username',$username)
            ->field('id')
            ->find();
        // 判断username 是否重名，剔除自己
        if (!empty($info['id']) && $info['id'] != $id){
            throw new JsonException(ErrorCode::DATA_REPEAT, "管理员已存在");
        }

        $status = isset($data['status']) ? $data['status'] : 0;
        $password = isset($data['password']) ? AdminModel::getPass($data['password']) : '';
        $auth_admin->username = $username;
        if ($password){
            $auth_admin->password = $password;
        }
        $auth_admin->status = $status;
        $result = $auth_admin->save();

        $roles = (isset($data['roles']) && is_array($data['roles'])) ? $data['roles'] : [];
        if (!$result){
            // 没有做任何更改
            $temp_roles = AuthRoleAdmin::where('admin_id',$id)->field('role_id')->select();
            if ($temp_roles){
                $temp_roles = $temp_roles->toArray();
                $temp_roles = array_column($temp_roles,'role_id');
            }
            // 没有差值，权限也没做更改
            if ($roles == $temp_roles){
                throw new JsonException(ErrorCode::DATA_CHANGE);
            }
        }


        if ($roles){
            // 先删除
            AuthRoleAdmin::where('admin_id',$id)->delete();
            $temp = [];
            foreach ($roles as $key => $value){
                $temp[$key]['role_id'] = $value;
                $temp[$key]['admin_id'] = $id;
            }
            //添加用户的角色
            $auth_role_admin = new AuthRoleAdmin();
            $auth_role_admin->saveAll($temp);
        }

        return ResultVo::success("SUCCESS");
    }

    /**
     * 删除
     */
    public function delete(){
        $id = request()->post('id/d');
        if (empty($id)){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $auth_admin = AuthAdmin::where('id',$id)->field('username')->find();
        if (!$auth_admin || $auth_admin['username'] == 'admin' || !$auth_admin->delete()){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }
        // 删除权限
        AuthRoleAdmin::where('admin_id',$id)->delete();

        return ResultVo::success("SUCCESS");

    }

}
