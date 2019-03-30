<?php

namespace app\admin\controller\file;

use app\admin\controller\Base;
use app\common\enums\ErrorCode;
use app\common\utils\PublicFileUtils;
use app\common\vo\ResultVo;
use think\facade\Env;
use think\File;

/**
 * 上传文件（管理文件的）
 * Class UploadFile
 * @package app\admin\controller
 */
class UploadController extends Base
{

    /**
     * 上传token
     */
    public function qiuNiuUpToken()
    {

        $res = [];
        $res["upload_url"] = PublicFileUtils::getUploadBaseUrl();
        $res["up_token"] = "xxxxxxxx";
        $res["domain"] = PublicFileUtils::getDomainBaseUrl();

        return ResultVo::success($res);
    }


    public function createFile()
    {
        /**
         * @var File $uploadFile
         */
        if (!request()->isPost()) {
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }

        // 上传文件
        $uploadName = request()->param('uploadName');
        $uploadName = !empty($uploadName) ? $uploadName : "file";
        $uploadFile = request()->file($uploadName);
        if (empty($uploadFile)) {
            return ResultVo::error(ErrorCode::DATA_NOT, "没有文件上传");
        }

        $exts = request()->param("exts");
        $size = request()->param("size/d");
        $filePath = request()->param("filePath");
        $config = [];
        if ($size > 0) {
            $config['size'] = $size;
        }
        if ($exts) {
            $config['ext'] = $exts;
        }
        $basepath = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $filePath = (!empty($filePath) ? $filePath : "resources") . DIRECTORY_SEPARATOR;
        $filepath = $basepath . $filePath;
        $info = $uploadFile->validate($config)->move($filepath);
        if (!$info) {
            return ResultVo::error(ErrorCode::DATA_NOT, $uploadFile->getError());
        }

        $saveName = $info->getSaveName();
        $path = $filePath . $saveName;
        $path = str_replace("\\", "/", $path);

        $res = [];
        $res["key"] = $path;
        return ResultVo::success($res);
    }

}