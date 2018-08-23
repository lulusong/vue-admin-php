<?php

namespace app\admin\controller\auth;

use app\admin\controller\BaseCheckUser;
use app\common\enums\ErrorCode;
use app\common\model\auth\AuthPermission;
use \app\common\model\auth\AuthPermissionRule;
use app\common\vo\ResultVo;

/**
 * 权限相关
 */
class PermissionRuleController extends BaseCheckUser
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
        $lists = AuthPermissionRule::getLists($where,$order);
        $merge_list = AuthPermissionRule::cateMerge($lists,'id','pid',0);
        $tree_list = AuthPermissionRule::cateTree($lists,'id','pid',0);
        $res['merge_list'] = $merge_list;
        $res['tree_list'] = $tree_list;
        return ResultVo::success($res);

    }

    /**
     * 添加
     */
    public function save(){
        $data = $this->request->post();
        if (empty($data['name']) || empty($data['status'])){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $name = strtolower(strip_tags($data['name']));
        // 菜单模型
        $info = AuthPermissionRule::where('name',$name)
            ->field('name')
            ->find();
        if ($info){
            return ResultVo::error(ErrorCode::DATA_REPEAT, "权限已经存在");
        }

        $now_time = time();
        $status = !empty($data['status']) ? $data['status'] : 0;
        $pid = !empty($data['pid']) ? $data['pid'] : 0;
        if ($pid){
            $info = AuthPermissionRule::where('id',$pid)
                ->field('id')
                ->find();
            if (!$info){
                return ResultVo::error(ErrorCode::NOT_NETWORK);
            }
        }
        $auth_permission_rule = new AuthPermissionRule();
        $auth_permission_rule->pid = $pid;
        $auth_permission_rule->name = $name;
        $auth_permission_rule->title = !empty($data['title']) ? $data['title'] : ' ';
        $auth_permission_rule->status = $status;
        $auth_permission_rule->condition = !empty($data['condition']) ? $data['condition'] : ' ';
        $auth_permission_rule->listorder = !empty($data['listorder']) ? strip_tags($data['listorder']) : 0;
        $auth_permission_rule->create_time = $now_time;
        $auth_permission_rule->update_time = $now_time;
        $result = $auth_permission_rule->save();

        if (!$result){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }
        return ResultVo::success($auth_permission_rule);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = $this->request->post();
        if (empty($data['id']) || empty($data['name'])){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $id = $data['id'];
        $name = strtolower(strip_tags($data['name']));
        // 模型
        $auth_permission_rule = AuthPermissionRule::where('id',$id)
            ->field('id')
            ->find();
        if (!$auth_permission_rule){
            return ResultVo::error(ErrorCode::DATA_NOT, "角色不存在");
        }

        $idInfo = AuthPermissionRule::where('name',$name)
            ->field('id')
            ->find();
        // 判断名称 是否重名，剔除自己
        if (!empty($idInfo['id']) && $idInfo['id'] != $id){
            return ResultVo::error(ErrorCode::DATA_REPEAT, "权限名称已存在");
        }

        $pid = isset($data['pid']) ? $data['pid'] : 0;
        // 判断父级是否存在
        if ($pid){
            $info = AuthPermissionRule::where('id',$pid)
                ->field('id')
                ->find();
            if (!$info){
                return ResultVo::error(ErrorCode::NOT_NETWORK);
            }
        }
        $AuthRuleList = AuthPermissionRule::all();
        // 查找当前选择的父级的所有上级
        $parents = AuthPermissionRule::queryParentAll($AuthRuleList,'id','pid',$pid);
        if (in_array($id,$parents)){
            return ResultVo::error(ErrorCode::NOT_NETWORK, "不能把自身/子级作为父级");
        }

        $status = isset($data['status']) ? $data['status'] : 0;
        $auth_permission_rule->pid = $pid;
        $auth_permission_rule->name = $name;
        $auth_permission_rule->title = !empty($data['title']) ? $data['title'] : ' ';
        $auth_permission_rule->status = $status;
        $auth_permission_rule->condition = !empty($data['condition']) ? $data['condition'] : ' ';
        $auth_permission_rule->listorder = !empty($data['listorder']) ? strip_tags($data['listorder']) : 0;
        $auth_permission_rule->update_time = time();
        $result = $auth_permission_rule->save();

        if (!$result){
            return ResultVo::error(ErrorCode::DATA_CHANGE);
        }

        return ResultVo::success("SUCCESS");
    }


    /**
     * 删除
     */
    public function delete(){
        $id = request()->post('id/d');
        if (empty($id)){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }

        // 下面有子节点，不能删除
        $sub = AuthPermissionRule::where('pid',$id)->field('id')->find();
        if ($sub){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        if (!AuthPermissionRule::where('id',$id)->delete()){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        // 删除授权的权限
        AuthPermission::where('permission_rule_id',$id)->delete();

        return ResultVo::success("SUCCESS");

    }

}
