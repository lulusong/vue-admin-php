<?php

namespace app\admin\controller\file;

use app\admin\controller\Base;
use app\admin\model\FileResourceTag;
use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\vo\ResultVo;

/**
 * 资源分组管理
 */
class ResourceTagController extends Base
{

    /**
     * 列表
     */
    public function index()
    {
        $where = [];
        $list = FileResourceTag::where($where)
            ->field('id,tag')
            ->select();
        return ResultVo::success($list);
    }

    /**
     * 添加
     */
    public function add() {

        $tag = request()->post('tag');
        if (empty($tag)){
            ResultVo::error(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }

        $file_resource_tag = new FileResourceTag();
        $file_resource_tag->tag = $tag;
        $file_resource_tag->create_time = date("Y-m-d H:i:s");
        $file_resource_tag->save();
        $file_resource_tag->id = intval($file_resource_tag->id);
        $file_resource_tag->create_time = time();
        return ResultVo::success($file_resource_tag);
    }


}