<?php

namespace app\admin\controller;

use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\vo\ResultVo;

/**
 * 资源分组管理
 * Class FileResourceTag
 * @package app\admin\controller
 */
class FileResourceTag extends Base
{

    /**
     * 列表
     */
    public function index()
    {
        $where = [];
        $list = \app\admin\model\FileResourceTag::where($where)
            ->field('id,tag')
            ->select();
        return json(ResultVo::success($list));
    }

    /**
     * 添加
     */
    public function add() {

        $tag = request()->post('tag');
        if (empty($tag)){
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }

        $ResourceTag = new \app\admin\model\FileResourceTag();
        $ResourceTag->tag = $tag;
        $ResourceTag->create_time = date("Y-m-d H:i:s");
        $ResourceTag->save();
        $ResourceTag->id = intval($ResourceTag->id);
        $ResourceTag->create_time = time();
        return json(ResultVo::success($ResourceTag));
    }


}