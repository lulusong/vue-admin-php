<?php

namespace app\admin\controller;

use app\common\enums\ErrorCode;
use \app\common\model\ad\Ad as AdModel;
use app\common\utils\PublicFileUtils;
use app\common\vo\ResultVo;

/**
 * 广告相关
 */
class AdController extends BaseCheckUser
{

    /**
     * 列表
     */
    public function index()
    {

        $where = [];
        $title = request()->get('title', '');
        if ($title !== ''){
            $where[] = ['title','=',$title];
        }
        $limit = request()->get('limit/d', 20);
        //分页配置
        $paginate = [
            'type' => 'bootstrap',
            'var_page' => 'page',
            'list_rows' => ($limit <= 0 || $limit > 20) ? 20 : $limit,
        ];
        $lists = AdModel::where($where)
            ->field('ad_id,title,describe,jump_type,link_url,pic,wxa_appid,wxa_path,extra_data,env_version,status')
            ->paginate($paginate);

        foreach ($lists as $k => $v) {
            $temp = $v;
            $temp['pic_url'] = PublicFileUtils::createUploadUrl($v['pic']);
            $temp['jump_type'] = !empty($v['jump_type']) ? $v['jump_type'] : '';
            $temp['link_url'] = !empty($v['link_url']) ? $v['link_url'] : '';
            $temp['wxa_appid'] = !empty($v['wxa_appid']) ? $v['wxa_appid'] : '';
            $temp['wxa_path'] = !empty($v['wxa_path']) ? $v['wxa_path'] : '';
            $temp['extra_data'] = !empty($v['extra_data']) ? $v['extra_data'] : '';
            $temp['env_version'] = !empty($v['env_version']) ? $v['env_version'] : '';
        }

        return ResultVo::success($lists);

    }

    /**
     * 添加
     */
    public function save(){
        $data = request()->post();
        if (empty($data['title']) || empty($data['jump_type']) || empty($data['pic'])){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $status = isset($data['status']) ? $data['status'] : 0;
        $ad = new AdModel();
        $ad->title = $data['title'];
        $ad->describe = !empty($data['describe']) ? $data['describe'] : '0';
        $ad->jump_type = $data['jump_type'];
        $ad->link_url = !empty($data['link_url']) ? $data['link_url'] : '0';
        $ad->pic = $data['pic'];
        $ad->wxa_appid = !empty($data['wxa_appid']) ? $data['wxa_appid'] : '0';
        $ad->wxa_path = !empty($data['wxa_path']) ? $data['wxa_path'] : '0';
        $ad->extra_data = !empty($data['extra_data']) ? $data['extra_data'] : '0';
        $ad->env_version = !empty($data['env_version']) ? $data['env_version'] : '0';
        $ad->status = $status;
        $ad->create_time = date("Y-m-d H:i:s");
        $ad->update_time = date("Y-m-d H:i:s");
        $result = $ad->save();

        if (!$result){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }
        return json($ad);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = request()->post();
        if (empty($data['ad_id']) || empty($data['title']) || empty($data['jump_type']) || empty($data['pic'])){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $ad_id = $data['ad_id'];
        // 模型
        $ad = AdModel::where('ad_id',$ad_id)
            ->field('ad_id')
            ->find();
        if (!$ad){
            return ResultVo::error(ErrorCode::DATA_NOT);
        }
        $status = isset($data['status']) ? $data['status'] : 0;
        $ad->title = $data['title'];
        $ad->describe = !empty($data['describe']) ? $data['describe'] : '0';
        $ad->jump_type = $data['jump_type'];
        $ad->link_url = !empty($data['link_url']) ? $data['link_url'] : '0';
        $ad->pic = $data['pic'];
        $ad->wxa_appid = !empty($data['wxa_appid']) ? $data['wxa_appid'] : '0';
        $ad->wxa_path = !empty($data['wxa_path']) ? $data['wxa_path'] : '0';
        $ad->extra_data = !empty($data['extra_data']) ? $data['extra_data'] : '0';
        $ad->env_version = !empty($data['env_version']) ? $data['env_version'] : '0';
        $ad->status = $status;
        $result = $ad->save();
        if (!$result){
            return ResultVo::error(ErrorCode::DATA_CHANGE);
        }

        return 'SUCCESS';
    }

    /**
     * 删除
     */
    public function delete(){
        $ad_id = request()->post('ad_id/d');
        if (empty($ad_id)){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        if (!AdModel::where('ad_id',$ad_id)->delete()){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        return 'SUCCESS';

    }

}
