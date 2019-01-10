<?php

namespace app\admin\controller\file;

use app\admin\controller\Base;
use app\admin\model\FileResource;
use app\common\enums\ErrorCode;
use app\common\utils\PublicFileUtils;
use app\common\vo\ResultVo;

/**
 * 资源管理
 */
class ResourceController extends Base
{

    /**
     * 列表
     */
    public function index()
    {
        $where = [];
        $type = request()->get('type/d', 0);
        $where[] = ['type', '=', $type];
        $tagId = request()->get('tagId/d', 0);
        if (!empty($tagId)) {
            $where[] = ['tag_id', '=', $tagId];
        }
        $size = request()->get('size/d', 20);
        //分页配置
        $paginate = [
            'type' => 'bootstrap',
            'var_page' => 'page',
            'list_rows' => ($size <= 0 || $size > 20) ? 20 : $size,
        ];
        $file_resource = new FileResource();
        $lists = $file_resource->where($where)
            ->field('id,type,filename,path,size,ext,create_time')
            ->paginate($paginate);

        foreach ($lists as $k => $v) {
            $v['url'] = PublicFileUtils::createUploadUrl($v['path']);
            $v['create_time'] = strtotime($v['create_time']);
            $lists[$k] = $v;
        }

        $res = [];
        $res["total"] = $lists->total();
        $res["list"] = $lists->items();
        return ResultVo::success($res);
    }

    /**
     * 添加
     */
    public function add()
    {
        $type = request()->param('type/d',0);
        $tag_id = request()->post('tagId/d',0);
        $filename = request()->post("filename");
        $path = request()->post("path");
        $path = $path ? $path : request()->post("key");
        if (!$path) {
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL, "文件路径不存在");
        }

        $size = request()->post("size/d");
        $ext = request()->post("ext");
        $file_resource = new FileResource();
        $file_resource->tag_id = $tag_id;
        $file_resource->type = $type;
        $file_resource->filename = $filename;
        $file_resource->path = $path;
        $file_resource->size = $size;
        $file_resource->ext = $ext;
        $file_resource->create_time = date("Y-m-d H:i:s");
        $file_resource->save();
        $res = [];
        $res["id"] = intval($file_resource->id);
        return ResultVo::success($res);
    }

}