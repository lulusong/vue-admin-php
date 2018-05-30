<?php

namespace app\admin\controller;

use app\admin\model\ErrorCode;

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
        return json($list);
    }

    /**
     * 添加
     */
    public function add() {

        $tag = request()->post('tag');
        if (empty($tag)){
            $res = [];
            $res['errcode'] = ErrorCode::$HTTP_METHOD_NOT_ALLOWED;
            $res['errmsg'] = 'Method Not Allowed';
            return json($res);
        }

        $ResourceTag = new \app\admin\model\FileResourceTag();
        $ResourceTag->tag = $tag;
        $ResourceTag->create_time = date("Y-m-d H:i:s");
        $ResourceTag->save();
        $ResourceTag->id = intval($ResourceTag->id);
        $ResourceTag->create_time = time();
        return json($ResourceTag);
    }


}