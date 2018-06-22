<?php

namespace app\admin\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\model\AuthPermission;
use app\common\model\AuthPermissionRule;
use \app\common\model\AuthRole;
use app\common\vo\ResultVo;

/**
 * 角色相关
 */
class AuthRoleController extends BaseCheckUser
{

    /**
     * 列表
     */
    public function index()
    {

        $where = [];
        $order = 'id ASC';
        $status = request()->get('status', '');
        if ($status !== ''){
            $where[] = ['status','=',intval($status)];
            $order = '';
        }
        $name = request()->get('name', '');
        if (!empty($name)){
            $where[] = ['name','like',$name . '%'];
            $order = '';
        }
        $lists = AuthRole::where($where)
            ->field('id,name,status,remark,create_time,listorder')
            ->order($order)
            ->select();

        return ResultVo::success($lists);

    }

    /*
     * 授权
     */
    public function auth(){
        if (request()->isGet()){
            $id = request()->get('id/d','');
            $auth_permission = AuthPermission::where('role_id',$id)
                ->field(['permission_rule_id'])
                ->select();
            $rule_list = AuthPermissionRule::getLists([],'id ASC');
            $checked_keys = [];
            foreach ($rule_list as $key=>$value){
                foreach ($auth_permission as $k=>$v){
                    if (strtolower($value['id']) == strtolower($v['permission_rule_id'])){
                        $checked_keys[] = $v['permission_rule_id'];
                    }
                }
            }

            $merge_list = AuthPermissionRule::cateMerge($rule_list,'id','pid',0);
            $res['auth_list'] = $merge_list;
            $res['checked_keys'] = $checked_keys;
            return ResultVo::success($res);
        }

        $data = request()->post();
        $role_id = isset($data['role_id']) ? $data['role_id'] : '';
        if (!$role_id){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }
        $auth_rules = isset($data['auth_rules']) ? $data['auth_rules'] : [];
        $rule_access = [];
        foreach ($auth_rules as $key=>$val){
            $rule_access[$key]['role_id'] = $role_id;
            $rule_access[$key]['permission_rule_id'] = $val;
            $rule_access[$key]['type'] = 'admin';
        }

        //先删除
        $auth_permission = new AuthPermission();
        $auth_permission->where(['role_id' => $role_id])->delete();
        if (!$rule_access || !$auth_permission->saveAll($rule_access)){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return ResultVo::success("SUCCESS");

    }

    /**
     * 添加
     */
    public function save(){
        $data = request()->post();
        if (empty($data['name']) || empty($data['status'])){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $name = $data['name'];
        // 菜单模型
        $info = AuthRole::where('name',$name)
            ->field('name')
            ->find();
        if ($info){
            throw new JsonException(ErrorCode::DATA_REPEAT);
        }

        $now_time = time();
        $status = isset($data['status']) ? $data['status'] : 0;
        $auth_role = new AuthRole();
        $auth_role->name = $name;
        $auth_role->status = $status;
        $auth_role->remark = isset($data['remark']) ? strip_tags($data['remark']) : '';
        $auth_role->create_time = $now_time;
        $auth_role->update_time = $now_time;
        $result = $auth_role->save();

        if (!$result){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return ResultVo::success($auth_role);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = request()->post();
        if (empty($data['id']) || empty($data['name'])){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $id = $data['id'];
        $name = strip_tags($data['name']);
        // 模型
        $auth_role = AuthRole::where('id',$id)
            ->field('id')
            ->find();
        if (!$auth_role){
            throw new JsonException(ErrorCode::DATA_NOT, "角色不存在");
        }

        $info = AuthRole::where('name',$name)
            ->field('id')
            ->find();
        // 判断角色名称 是否重名，剔除自己
        if (!empty($info['id']) && $info['id'] != $id){
            throw new JsonException(ErrorCode::DATA_REPEAT);
        }

        $status = isset($data['status']) ? $data['status'] : 0;
        $auth_role->name = $name;
        $auth_role->status = $status;
        $auth_role->remark = isset($data['remark']) ? strip_tags($data['remark']) : '';
        $auth_role->update_time = time();
        $auth_role->listorder = isset($data['listorder']) ? intval($data['listorder']) : 999;
        $result = $auth_role->save();

        if (!$result){
            throw new JsonException(ErrorCode::DATA_CHANGE);
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
        if (!AuthRole::where('id',$id)->delete()){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return ResultVo::success("SUCCESS");

    }

}
