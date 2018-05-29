<?php

namespace app\admin\controller;

use app\admin\model\ErrorCode;
use think\File;

/**
 * 资源管理
 * Class FileResource
 * @package app\admin\controller
 */
class FileResource extends Base
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
        $Resource = new \app\admin\model\FileResource();
        $lists = $Resource->where($where)
            ->field('id,type,filename,path,size,ext,create_time')
            ->paginate($paginate);

        foreach ($lists as $k => $v) {
            $v['url'] = $Resource::getUrl($v['path']);
            $v['create_time'] = strtotime($v['create_time']);
            $lists[$k] = $v;
        }
        return json($lists);
    }

    /**
     * 添加
     */
    public function add()
    {
        /**
         * @var File $uploadFile
         */
        if (!request()->isPost()) {
            $res = [];
            $res['errcode'] = ErrorCode::$HTTP_METHOD_NOT_ALLOWED;
            $res['errmsg'] = 'Method Not Allowed';
            return json($res);
        }
        $type = request()->param('type/d',0);
        $tag_id = request()->post('tagId/d',0);

        // 上传文件
        $uploadName = request()->param('uploadName');
        $uploadFile = request()->file($uploadName);
        if (empty($uploadFile)) {
            $res = [];
            $res['errcode'] = ErrorCode::$DATA_NOT;
            $res['errmsg'] = '没有文件上传~';
            $res['ss'] = $uploadName;
            return json($res);
        }

        $exts = request()->param("exts");
        $size = request()->param("size/d");
        $config = [];
        if ($size > 0) {
            $config['size'] = $size;
        }
        if ($exts) {
            $config['ext'] = $exts;
        }
        $basepath = \app\admin\model\FileResource::getBasePath();
        $resource_path = \app\admin\model\FileResource::$RESOURCES_PATH . \app\admin\model\FileResource::getTypePath($type);
        $filepath = $basepath . $resource_path ;
        $info = $uploadFile->validate($config)->move($filepath);
        if (!$info) {
            $res['errcode'] = ErrorCode::$DATA_NOT;
            $res['errmsg'] = $uploadFile->getError();
            return json($res);
        }
        $saveName = $info->getSaveName();
        $path = $resource_path . $saveName;
        $path = str_replace("\\", "/", $path);
        $Resource = new \app\admin\model\FileResource();
        $Resource->tag_id = $tag_id;
        $Resource->type = $type;
        $Resource->filename = $uploadFile->getInfo('name');
        $Resource->path = $path;
        $Resource->size = $info->getSize();
        $Resource->ext = $info->getExtension();
        $Resource->create_time = date("Y-m-d H:i:s");
        $Resource->save();
        $Resource->create_time = time();
        $Resource->url = \app\admin\model\FileResource::getUrl($path);
        $Resource->id = intval($Resource->id);
        return json($Resource);
    }

}