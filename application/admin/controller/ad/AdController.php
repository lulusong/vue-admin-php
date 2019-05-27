<?php

namespace app\admin\controller\ad;

use app\admin\controller\BaseCheckUser;
use app\common\enums\ErrorCode;
use \app\common\model\ad\Ad;
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
        $lists = Ad::where($where)
            ->field('ad_id,title,describe,pic,jump_type,jump_url,ios_url,android_url,wxa_appid,
                channel_type,channel_list,android_version_type,android_version_list,ios_version_type,ios_version_list,
                new_show_start_num,new_show_max_num,old_show_start_num,old_show_max_num,
                start_time,end_time,event_name,status')
            ->order("ad_id DESC")
            ->paginate($paginate);

        foreach ($lists as $k => $v) {
            $v['pic_url'] = PublicFileUtils::createUploadUrl($v['pic']);
            $v["channel_list"] = !empty($v["channel_list"]) ? explode(",", $v["channel_list"]) : [];
            $v["android_version_list"] = !empty($v["android_version_list"]) ? explode(",", $v["android_version_list"]) : [];
            $v["ios_version_list"] = !empty($v["ios_version_list"]) ? explode(",", $v["ios_version_list"]) : [];
        }

        $res = [];
        $res["total"] = $lists->total();
        $res["list"] = $lists->items();
        return ResultVo::success($res);

    }
    /**
     * 添加
     */
    public function save(){
        $data = request()->post();
        if (
            empty($data['title'])
            || !isset($data['jump_type'])
            || empty($data['pic'])
            || $data['jump_type'] < 0
            || $data['jump_type'] > 2
            || ($data['jump_type'] == 0 && empty($data["jump_url"])) // 如果是 web 跳转类型，则 跳转链接必须有值
            || ($data['jump_type'] == 1 && empty($data["ios_url"]) && empty($data["android_url"])) // 如果是 APP内 跳转类型，则 ios 和 android 的链接必须一个有值
            || ($data['jump_type'] == 2 && empty($data["jump_url"]) && empty($data["wxa_appid"])) // 如果是 小程序 跳转类型，则 跳转链接 和 小程序id 必须有值
            // 渠道名单
            || (!empty($data["channel_type"]) && empty($data['channel_list']))
            || (!empty($data['channel_list']) && count($data['channel_list']) > 15)
            // Android 版本名单
            || (!empty($data["android_version_type"]) && empty($data['android_version_list']))
            || (!empty($data['android_version_list']) && count($data['android_version_list']) > 15)
            // Ios 版本名单
            || (!empty($data["ios_version_type"]) && empty($data['ios_version_list']))
            || (!empty($data['ios_version_list']) && count($data['ios_version_list']) > 15)
        ){
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }
        $status = isset($data['status']) ? $data['status'] : 0;
        $ad = new Ad();
        $ad->title = $data['title'];
        $ad->describe = !empty($data['describe']) ? $data['describe'] : '';
        $ad->pic = $data['pic'];
        $ad->jump_type = intval($data['jump_type']);
        $ad->jump_url = !empty($data['jump_url']) ? $data['jump_url'] : '';
        $ad->ios_url = !empty($data['ios_url']) ? $data['ios_url'] : '';
        $ad->android_url = !empty($data['android_url']) ? $data['android_url'] : '';
        $ad->wxa_appid = !empty($data['wxa_appid']) ? $data['wxa_appid'] : '';

        // 渠道名单
        // 去掉 逗号 字符串
        $channel_list = [];
        if (!empty($data['channel_list'])) {
            foreach ($data['channel_list'] as $k => $v) {
                $channel_list[] = str_replace(",", "", trim($v));
            }
        }
        $channel_list = array_unique(array_filter($channel_list));
        $ad->channel_list = implode(",", $channel_list);
        $ad->channel_type = !empty($data['channel_type']) && $data['channel_type'] <= 2 && $data['channel_type'] >= 0 ? intval($data['channel_type']) : 0;

        // Android版本名单
        // 去掉 逗号 字符串
        $android_version_list = [];
        if (!empty($data['android_version_list'])) {
            foreach ($data['android_version_list'] as $k => $v) {
                $android_version_list[] = str_replace(",", "", trim($v));
            }
        }
        $android_version_list = array_unique(array_filter($android_version_list));
        $ad->android_version_list = implode(",", $android_version_list);
        $ad->android_version_type = !empty($data['android_version_type']) && $data['android_version_type'] <= 2 && $data['android_version_type'] >= 0 ? intval($data['android_version_type']) : 0;

        // Ios版本名单
        // 去掉 逗号 字符串
        $ios_version_list = [];
        if (!empty($data['ios_version_list'])) {
            foreach ($data['ios_version_list'] as $k => $v) {
                $ios_version_list[] = str_replace(",", "", trim($v));
            }
        }
        $ios_version_list = array_unique(array_filter($ios_version_list));
        $ad->ios_version_list = implode(",", $ios_version_list);
        $ad->ios_version_type = !empty($data['ios_version_type']) && $data['ios_version_type'] <= 2 && $data['ios_version_type'] >= 0 ? intval($data['ios_version_type']) : 0;


        // 新老用户的展示逻辑
        $ad->new_show_start_num = !empty($data["new_show_start_num"]) ? intval($data["new_show_start_num"]) : 0;
        $ad->new_show_max_num = !empty($data["new_show_max_num"]) ? intval($data["new_show_max_num"]) : 0;
        $ad->old_show_start_num = !empty($data["old_show_start_num"]) ? intval($data["old_show_start_num"]) : 0;
        $ad->old_show_max_num = !empty($data["old_show_max_num"]) ? intval($data["old_show_max_num"]) : 0;


        $ad->start_time = !empty($data['start_time']) && strtotime($data['start_time']) ? date("Y-m-d H:i:s", strtotime($data['start_time'])) : null;
        $ad->end_time = !empty($data['end_time']) && strtotime($data['end_time']) ? date("Y-m-d H:i:s", strtotime($data['end_time'])) : null;
        $ad->status = $status;
        $ad->create_time = date("Y-m-d H:i:s");
        $ad->modified_time = date("Y-m-d H:i:s");
        $result = $ad->save();

        if (!$result){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        $res = [];
        $res["ad_id"] = intval($ad->ad_id);
        return ResultVo::success($res);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = request()->post();
        if (empty($data['ad_id'])
            || empty($data['title'])
            || !isset($data['jump_type'])
            || empty($data['pic'])
            || $data['jump_type'] < 0
            || $data['jump_type'] > 2
            || ($data['jump_type'] == 0 && empty($data["jump_url"])) // 如果是 web 跳转类型，则 跳转链接必须有值
            || ($data['jump_type'] == 1 && empty($data["ios_url"]) && empty($data["android_url"])) // 如果是 APP内 跳转类型，则 ios 和 android 的链接必须一个有值
            || ($data['jump_type'] == 2 && empty($data["jump_url"]) && empty($data["wxa_appid"])) // 如果是 小程序 跳转类型，则 跳转链接 和 小程序id 必须有值
            // 渠道名单
            || (!empty($data["channel_type"]) && empty($data['channel_list']))
            || (!empty($data['channel_list']) && count($data['channel_list']) > 15)
            // Android 版本名单
            || (!empty($data["android_version_type"]) && empty($data['android_version_list']))
            || (!empty($data['android_version_list']) && count($data['android_version_list']) > 15)
            // Ios 版本名单
            || (!empty($data["ios_version_type"]) && empty($data['ios_version_list']))
            || (!empty($data['ios_version_list']) && count($data['ios_version_list']) > 15)
        ){
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }
        $ad_id = $data['ad_id'];
        // 模型
        $ad = Ad::where('ad_id',$ad_id)
            ->field('ad_id')
            ->find();
        if (!$ad){
            return ResultVo::error(ErrorCode::DATA_NOT);
        }

        $ad->title = $data['title'];
        $ad->describe = !empty($data['describe']) ? $data['describe'] : '';
        $ad->pic = $data['pic'];
        $ad->jump_type = intval($data['jump_type']);
        $ad->jump_url = !empty($data['jump_url']) ? $data['jump_url'] : '';
        $ad->ios_url = !empty($data['ios_url']) ? $data['ios_url'] : '';
        $ad->android_url = !empty($data['android_url']) ? $data['android_url'] : '';
        $ad->wxa_appid = !empty($data['wxa_appid']) ? $data['wxa_appid'] : '';

        // 渠道名单
        // 去掉 逗号 字符串
        $channel_list = [];
        if (!empty($data['channel_list'])) {
            foreach ($data['channel_list'] as $k => $v) {
                $channel_list[] = str_replace(",", "", trim($v));
            }
        }
        $channel_list = array_unique(array_filter($channel_list));
        $ad->channel_list = implode(",", $channel_list);
        $ad->channel_type = !empty($data['channel_type']) && $data['channel_type'] <= 2 && $data['channel_type'] >= 0 ? intval($data['channel_type']) : 0;

        // Android版本名单
        // 去掉 逗号 字符串
        $android_version_list = [];
        if (!empty($data['android_version_list'])) {
            foreach ($data['android_version_list'] as $k => $v) {
                $android_version_list[] = str_replace(",", "", trim($v));
            }
        }
        $android_version_list = array_unique(array_filter($android_version_list));
        $ad->android_version_list = implode(",", $android_version_list);
        $ad->android_version_type = !empty($data['android_version_type']) && $data['android_version_type'] <= 2 && $data['android_version_type'] >= 0 ? intval($data['android_version_type']) : 0;

        // Ios版本名单
        // 去掉 逗号 字符串
        $ios_version_list = [];
        if (!empty($data['ios_version_list'])) {
            foreach ($data['ios_version_list'] as $k => $v) {
                $ios_version_list[] = str_replace(",", "", trim($v));
            }
        }
        $ios_version_list = array_unique(array_filter($ios_version_list));
        $ad->ios_version_list = implode(",", $ios_version_list);
        $ad->ios_version_type = !empty($data['ios_version_type']) && $data['ios_version_type'] <= 2 && $data['ios_version_type'] >= 0 ? intval($data['ios_version_type']) : 0;


        // 新老用户的展示逻辑
        $ad->new_show_start_num = !empty($data["new_show_start_num"]) ? intval($data["new_show_start_num"]) : 0;
        $ad->new_show_max_num = !empty($data["new_show_max_num"]) ? intval($data["new_show_max_num"]) : 0;
        $ad->old_show_start_num = !empty($data["old_show_start_num"]) ? intval($data["old_show_start_num"]) : 0;
        $ad->old_show_max_num = !empty($data["old_show_max_num"]) ? intval($data["old_show_max_num"]) : 0;


        $ad->start_time = !empty($data['start_time']) && strtotime($data['start_time']) ? date("Y-m-d H:i:s", strtotime($data['start_time'])) : null;
        $ad->end_time = !empty($data['end_time']) && strtotime($data['end_time']) ? date("Y-m-d H:i:s", strtotime($data['end_time'])) : null;
        $status = isset($data['status']) ? $data['status'] : 0;
        $ad->status = $status;
        $ad->create_time = date("Y-m-d H:i:s");
        $ad->modified_time = date("Y-m-d H:i:s");
        $result = $ad->save();
        if (!$result){
            return ResultVo::error(ErrorCode::DATA_CHANGE);
        }

        return ResultVo::success();
    }

    /**
     * 删除
     */
    public function delete(){
        $ad_id = request()->post('ad_id/d');
        if (empty($ad_id)){
            return ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        if (!Ad::where('ad_id',$ad_id)->delete()){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }

        return ResultVo::success();

    }

}
