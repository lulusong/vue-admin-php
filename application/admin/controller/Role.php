<?php

namespace app\admin\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\model\AuthAccess;
use app\common\model\AuthRule;
use \app\common\model\Role as RoleModel;
use app\common\vo\ResultVo;

/**
 * 角色相关
 */
class Role extends BaseCheckUser
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
        $lists = RoleModel::where($where)
            ->field('id,name,status,remark,create_time,listorder')
            ->order($order)
            ->select();

        return json(ResultVo::success($lists));

    }

    public function auth(){
        if (request()->isGet()){
            $id = request()->get('id/d','');
            $auth_access = AuthAccess::where('role_id',$id)
                ->field(['auth_rule_id'])
                ->select();
            $rule_list = AuthRule::getLists([],'id ASC');
            $checked_keys = [];
            foreach ($rule_list as $key=>$value){
                foreach ($auth_access as $k=>$v){
                    if (strtolower($value['id']) == strtolower($v['auth_rule_id'])){
                        $checked_keys[] = $v['auth_rule_id'];
                    }
                }
            }

            $merge_list = AuthRule::cateMerge($rule_list,'id','pid',0);
            $res['auth_list'] = $merge_list;
            $res['checked_keys'] = $checked_keys;
            return json(ResultVo::success($res));
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
            $rule_access[$key]['auth_rule_id'] = $val;
            $rule_access[$key]['type'] = 'admin';
        }

        //先删除
        $AuthAccess = new AuthAccess();
        $AuthAccess->where(['role_id' => $role_id])->delete();
        if (!$rule_access || !$AuthAccess->saveAll($rule_access)){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return json(ResultVo::success("SUCCESS"));

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
        $info = RoleModel::where('name',$name)
            ->field('name')
            ->find();
        if ($info){
            throw new JsonException(ErrorCode::DATA_REPEAT);
        }

        $now_time = time();
        $status = isset($data['status']) ? $data['status'] : 0;
        $RoleModel = new RoleModel();
        $RoleModel->name = $name;
        $RoleModel->status = $status;
        $RoleModel->remark = isset($data['remark']) ? strip_tags($data['remark']) : '';
        $RoleModel->create_time = $now_time;
        $RoleModel->update_time = $now_time;
        $result = $RoleModel->save();

        if (!$result){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        $res['id'] = $RoleModel->getLastInsID();
        $res['name'] = $RoleModel->name;
        $res['status'] = $RoleModel->status;
        $res['remark'] = $RoleModel->remark;
        $res['create_time'] = $RoleModel->create_time;

        return json(ResultVo::success($res));
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
        $RoleModel = RoleModel::where('id',$id)
            ->field('id')
            ->find();
        if (!$RoleModel){
            throw new JsonException(ErrorCode::DATA_NOT, "角色不存在");
        }

        $info = RoleModel::where('name',$name)
            ->field('id')
            ->find();
        // 判断角色名称 是否重名，剔除自己
        if (!empty($info['id']) && $info['id'] != $id){
            throw new JsonException(ErrorCode::DATA_REPEAT);
        }

        $status = isset($data['status']) ? $data['status'] : 0;
        $RoleModel->name = $name;
        $RoleModel->status = $status;
        $RoleModel->remark = isset($data['remark']) ? strip_tags($data['remark']) : '';
        $RoleModel->update_time = time();
        $RoleModel->listorder = isset($data['listorder']) ? intval($data['listorder']) : 999;
        $result = $RoleModel->save();

        if (!$result){
            throw new JsonException(ErrorCode::DATA_CHANGE);
        }


        return json(ResultVo::success("SUCCESS"));
    }


    /**
     * 删除
     */
    public function delete(){
        $id = request()->post('id/d');
        if (empty($id)){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        if (!RoleModel::where('id',$id)->delete()){
            throw new JsonException(ErrorCode::NOT_NETWORK);
        }

        return json(ResultVo::success("SUCCESS"));

    }

}
