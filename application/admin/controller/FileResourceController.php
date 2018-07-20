<?php

namespace app\admin\controller;

use app\admin\model\FileResource;
use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\utils\PublicFileUtils;
use app\common\vo\ResultVo;
use think\File;

/**
 * 资源管理
 */
class FileResourceController extends Base
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
        return ResultVo::success($lists);
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
            throw new JsonException(ErrorCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $type = request()->param('type/d',0);
        $tag_id = request()->post('tagId/d',0);

        // 上传文件
        $uploadName = request()->param('uploadName');
        $uploadFile = request()->file($uploadName);
        if (empty($uploadFile)) {
            throw new JsonException(ErrorCode::DATA_NOT, "没有文件上传");
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
        $basepath = FileResource::getBasePath();
        $resource_path = FileResource::$RESOURCES_PATH . FileResource::getTypePath($type);
        $filepath = $basepath . $resource_path ;
        $info = $uploadFile->validate($config)->move($filepath);
        if (!$info) {
            throw new JsonException(ErrorCode::DATA_NOT, $uploadFile->getError());
        }
        $saveName = $info->getSaveName();
        $path = $resource_path . $saveName;
        $path = str_replace("\\", "/", $path);
        $file_resource = new FileResource();
        $file_resource->tag_id = $tag_id;
        $file_resource->type = $type;
        $file_resource->filename = $uploadFile->getInfo('name');
        $file_resource->path = $path;
        $file_resource->size = $info->getSize();
        $file_resource->ext = $info->getExtension();
        $file_resource->create_time = date("Y-m-d H:i:s");
        $file_resource->save();
        $file_resource->create_time = time();
        $file_resource->url = PublicFileUtils::createUploadUrl($path);
        $file_resource->id = intval($file_resource->id);
        return ResultVo::success($file_resource);
    }

}