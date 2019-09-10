<?php

namespace app\admin\controller\ad;

use app\admin\controller\BaseCheckUser;
use app\common\enums\ErrorCode;
use app\common\model\ad\Ad;
use \app\common\model\ad\AdSite;
use app\common\vo\ResultVo;

/**
 * 广告位相关
 */
class SiteController extends BaseCheckUser
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
        $lists = AdSite::where($where)
            ->field('site_id,site_name,describe,ad_ids,modified_time')
            ->paginate($paginate);
        $where_ad_ids = [];
        foreach ($lists as $v) {
            $ad_ids = !empty($v['ad_ids']) ? explode(",", $v['ad_ids']) : [];
            foreach ($ad_ids as $key => $val) {
                $ad_ids[$key] = intval($val);
                $where_ad_ids[] = intval($val);
            }
            $v['ad_ids'] = $ad_ids;
        }

        $ad_list = Ad::whereIn("ad_id", $where_ad_ids)
            ->field("ad_id,title,describe,status")
            ->limit(150)
            ->select();

        foreach ($lists as $v) {
            $ads = [];
            if (!empty($v['ad_ids'])) {
                $temp_ads = [];
                foreach ($ad_list as $k1=>$v1) {
                    if (in_array($v1["ad_id"], $v["ad_ids"])) {
                        $temp_ads[$v1["ad_id"]] = $v1;
                    }
                }
                foreach ($v['ad_ids'] as $v1) {
                    if (!empty($temp_ads[$v1])) {
                        $ads[] = $temp_ads[$v1];
                    }
                }
            }
            $v["ads"] = $ads;
        }


        $res = [];
        $res["total"] = $lists->total();
        $res["list"] = $lists->items();
        return ResultVo::success($res);

    }

    public function adList()
    {
        $ad_ids = request()->get('ad_ids');
        $res = [];
        $res["data"] = [];
        if (empty($ad_ids) || !is_array($ad_ids)) {
            return ResultVo::success($res);
        }

        foreach ($ad_ids as $k=>$v) {
            $ad_ids[$k] = intval($v);
        }
        $ad_ids = array_unique(array_filter($ad_ids));

        $ad_list = Ad::whereIn("ad_id", $ad_ids)
            ->where("status", 1)
            ->field("ad_id,title,describe,status")
            ->select();
        $res = [];
        $res["total"] = count($ad_list);
        $res["list"] = $ad_list;
        return ResultVo::success($res);

    }

    /**
     * 添加
     */
    public function save(){
        $data = request()->post();
        if (empty($data['site_name'])){
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }
        $ad_site = new AdSite();
        $ad_site->site_name = $data['site_name'];
        if (!empty($data['describe'])) {
            $ad_site->describe = $data['describe'];
        }
        if (!empty($data['ad_ids']) && is_array($data['ad_ids'])) {
            $ad_ids = $data['ad_ids'];
            foreach ($ad_ids as $k=>$v) {
                $ad_ids[$k] = intval($v);
            }
            $ad_ids = array_unique(array_filter($ad_ids));
            if (!empty($ad_ids)) {
                $ad_site->ad_ids = implode(",", $ad_ids);
            }
        }
        $ad_site->create_time = date("Y-m-d H:i:s");
        $ad_site->modified_time = date("Y-m-d H:i:s");
        $result = $ad_site->save();

        if (!$result){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        $res = [];
        $res["site_id"] = intval($ad_site->site_id);
        return ResultVo::success($res);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = request()->post();
        if (empty($data['site_id']) || empty($data['site_name'])){
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }
        $site_id = $data['site_id'];
        // 模型
        $ad_site = AdSite::where('site_id',$site_id)
            ->field('site_id')
            ->find();
        if (!$ad_site){
            return ResultVo::error(ErrorCode::DATA_NOT);
        }
        $ad_site->site_name = $data['site_name'];
        if (!empty($data['describe'])) {
            $ad_site->describe = $data['describe'];
        }
        if (!empty($data['ad_ids']) && is_array($data['ad_ids'])) {
            $ad_ids = $data['ad_ids'];
            foreach ($ad_ids as $k=>$v) {
                $ad_ids[$k] = intval($v);
            }
            $ad_ids = array_unique(array_filter($ad_ids));
            if (!empty($ad_ids)) {
                $ad_site->ad_ids = implode(",", $ad_ids);
            }
        } else {
            $ad_site->ad_ids = "";
        }
        $ad_site->modified_time = date("Y-m-d H:i:s");
        $result = $ad_site->save();
        if (!$result){
            return ResultVo::error(ErrorCode::DATA_CHANGE);
        }

        return ResultVo::success();
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
        if (!AdSite::where('site_id',$site_id)->delete()){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        return ResultVo::success();

    }

}
