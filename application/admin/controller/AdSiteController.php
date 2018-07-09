<?php

namespace app\admin\controller;

use app\common\enums\ErrorCode;
use app\common\model\ad\Ad;
use \app\common\model\ad\AdSite as AdSiteModel;
use app\common\vo\ResultVo;

/**
 * 广告位相关
 */
class AdSiteController extends BaseCheckUser
{

    /**
     * 列表
     */
    public function index()
    {

        $where = [];
        $site_id = request()->get('site_id/d', '');
        if ($site_id !== ''){
            $where[] = ['site_id','=',intval($site_id)];
        }
        $limit = request()->get('limit/d', 20);
        //分页配置
        $paginate = [
            'type' => 'bootstrap',
            'var_page' => 'page',
            'list_rows' => ($limit <= 0 || $limit > 20) ? 20 : $limit,
        ];
        $lists = AdSiteModel::where($where)
            ->field('site_id,site_name,describe,ad_ids,update_time')
            ->paginate($paginate);
        foreach ($lists as $v) {
            $ad_ids = !empty($v['ad_ids']) ? explode(",", $v['ad_ids']) : [];
            foreach ($ad_ids as $key => $val) {
                $ad_ids[$key] = intval($val);
            }
            $v['ad_ids'] = $ad_ids;
        }
        return ResultVo::success($lists);

    }

    /**
     * 给广告位选择广告时调用
     */
    public function adList() {
        $where = [];
        $limit = request()->get('adLimit/d', 20);
        //分页配置
        $paginate = [
            'type' => 'bootstrap',
            'var_page' => 'adPage',
            'list_rows' => ($limit <= 0 || $limit > 20) ? 20 : $limit,
        ];
        // 查询当前广告位的广告id
        $ad_ids = request()->get('ad_ids');
        $ad_ids = !empty($ad_ids) ? explode(",", $ad_ids) : [];
        $lists = Ad::where($where)
            ->field('ad_id,title,describe,status')
            ->paginate($paginate);
        $data = [];
        foreach ($lists as $k => $v) {
            $temp = [];
            $temp['key'] = $v['ad_id'];
            $temp['label'] = $v['title'] . '-' . $v['describe'];
            $temp['disabled'] = $v['status'] !== 1;
            $temp['describe'] = $v['describe'];
            $data[] = $temp;
            foreach ($ad_ids as $key => $val) {
                if ($v['ad_id'] == $val) {
                    unset($ad_ids[$key]);
                }
            }
        }
        if (count($lists) > 0 && $ad_ids) {
            $temp_data = Ad::whereIn('ad_id', $ad_ids)
                ->field('ad_id,title,describe,status')
                ->select();
            foreach ($temp_data as $k => $v) {
                $temp = [];
                $temp['key'] = $v['ad_id'];
                $temp['label'] = $v['title'] . '-' . $v['describe'];
                $temp['disabled'] = $v['status'] !== 1;
                $temp['describe'] = $v['describe'];
                $data[] = $temp;
            }
        }

        return ResultVo::success($data);
    }

    /**
     * 添加
     */
    public function save(){
        $data = request()->post();
        if (empty($data['site_name'])){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $ad_site = new AdSiteModel();
        $ad_site->site_name = $data['site_name'];
        $ad_site->describe = !empty($data['describe']) ? $data['describe'] : ' ';
        $ad_site->ad_ids = !empty($data['ad_ids']) ? implode(",", $data['ad_ids']) : '0';
        $ad_site->create_time = date("Y-m-d H:i:s");
        $ad_site->update_time = date("Y-m-d H:i:s");
        $result = $ad_site->save();

        if (!$result){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }
        return json($ad_site);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = request()->post();
        if (empty($data['site_id']) || empty($data['site_name'])){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $site_id = $data['site_id'];
        // 模型
        $ad_site = AdSiteModel::where('site_id',$site_id)
            ->field('site_id')
            ->find();
        if (!$ad_site){
            return ResultVo::error(ErrorCode::DATA_NOT);
        }
        $ad_site->site_name = $data['site_name'];
        $ad_site->describe = !empty($data['describe']) ? $data['describe'] : ' ';
        $ad_site->ad_ids = !empty($data['ad_ids']) ? implode(",", $data['ad_ids']) : '0';
        $result = $ad_site->save();
        if (!$result){
            return ResultVo::error(ErrorCode::DATA_CHANGE);
        }

        return 'SUCCESS';
    }

    /**
     * 删除
     */
    public function delete(){
        $site_id = request()->post('site_id/d');
        if (empty($site_id)){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        return ResultVo::error(ErrorCode::NOT_NETWORK, "此功能目前不开放");
        if (!AdSiteModel::where('site_id',$site_id)->delete()){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        return 'SUCCESS';

    }

}
