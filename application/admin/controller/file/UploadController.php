<?php

namespace app\admin\controller\file;

use app\admin\controller\Base;
use app\common\exception\JsonException;
use app\common\enums\ErrorCode;
use app\common\utils\PublicFileUtils;
use app\common\vo\ResultVo;
use think\facade\Env;
use think\File;

/**
 * // TODO 基本废弃
 * 上传文件（管理文件的）
 * Class UploadFile
 * @package app\admin\controller
 */
class UploadController extends Base
{
    /**
     * 文件对应的class name
     * @var array
     */
    private static $extClaseArr = [
        'html' => 'web',
        'js' => 'fileicon-sys-s-code',
        'css' => 'fileicon-sys-s-code',
        'txt' => 'fileicon-small-txt',
        'gif' => 'fileicon-small-pic',
        'png' => 'fileicon-small-pic',
        'jpg' => 'fileicon-small-pic',
        'zip' => 'fileicon-small-zip',
    ];

    public static $RESOURCES_PATH = 'resources' . DIRECTORY_SEPARATOR;

    /**
     * 获取上传文件的根路径
     */
    private static function getBasePath()
    {
        return Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    }

    public function newDir()
    {
        // 创建目录
        $pathName = request()->post("pathName");
        $basePath = self::getBasePath();
        $pathName = self::$RESOURCES_PATH . $pathName;
        $pathName = trim($pathName, '/'); // 去掉最前或者最后的 /
        $pathName = trim($pathName, '\\'); // 去掉最前或者最后的 \
        $dirname = $basePath . $pathName;
        $dirname = str_replace(' ', '', $dirname);
        if (!file_exists($dirname)) {
            // 目录不存在
            return ResultVo::error(ErrorCode::DATA_NOT, "目录不存在");
        }
        $filename = request()->post('filename');
        $filename = trim($filename, DIRECTORY_SEPARATOR); // 去掉最后一个 / 并且加上一个 /
        $dirname = $dirname . DIRECTORY_SEPARATOR . $filename;
        $dirname = str_replace(' ', '', $dirname);
        if (file_exists($dirname)) {
            // 目录已存在
            return ResultVo::error(ErrorCode::DATA_NOT, "文件夹已存在");
        }
        try {
            // 如果含有中文
            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $dirname) > 0) {
                return ResultVo::error(ErrorCode::DATA_NOT, "不能含有中文");
            }
            $dirname = str_replace(' ', '', $dirname);
            if (!mkdir($dirname, 0755, true)) {
                // 目录不存在
                return ResultVo::error(ErrorCode::DATA_NOT, "无权限创建目录");
            }
        } catch (\Exception $exception) {
            return ResultVo::error(ErrorCode::DATA_NOT, "无权限创建");
        }
        $path = $pathName . '/' . $filename;
        $path = str_replace("\\", "/", $path);
        $res = array(
            "path" => $path,
            "filename" => $filename,
            "className" => '',
            'url' => PublicFileUtils::createUploadUrl($path),
            'mtime' => time(),
            "is_dir" => 1,
            "fileExt" => '',
            "size" => 0
        );

        return ResultVo::success($res);
    }

    /**
     * 上传图片
     * @return \think\response\Json
     * @throws JsonException
     */
    public function newFile()
    {
        /**
         * @var File $uploadFile
         */
        // 上传文件
        $uploadName = request()->param('uploadName');
        $uploadFile = request()->file($uploadName);
        if (empty($uploadFile)) {
            return ResultVo::error(ErrorCode::DATA_NOT, "没有文件上传");
        }
        $pinYinName = request()->param('pinYinName', '');
        // 如果没有拼音的名称并且含有中文
        if (!$pinYinName && preg_match('/[\x{4e00}-\x{9fa5}]/u', $uploadFile->getInfo('name')) > 0) {
            return ResultVo::error(ErrorCode::DATA_NOT, "不能含有中文");
        }
        $pathName = request()->param("pathName");
        $pathName = trim($pathName, '/'); // 去掉最前或者最后的 /
        $pathName = trim($pathName, '\\'); // 去掉最前或者最后的 \
        $pathName = self::$RESOURCES_PATH . $pathName;
        $basePath = self::getBasePath();
        $dirname = $basePath . $pathName;
        if (!is_dir(dirname($dirname))) {
            // 目录不存在
            return ResultVo::error(ErrorCode::DATA_NOT, "目录不存在");
        }
        $exts = request()->param("exts");
        $size = request()->param("size/d");
        $path = $pathName ? $pathName . DIRECTORY_SEPARATOR : $pathName;
        $config = [];
        if ($size > 0) {
            $config['size'] = $size;
        }
        if ($exts) {
            $config['ext'] = $exts;
        }
        // 如果拼音的名称为空，则用户本来的名称
        $savename = !$pinYinName ? $uploadFile->getInfo('name') : $pinYinName;
        $savename = str_replace(' ', '', $savename);
        //dump($file);exit;
        // 移动到框架应用根目录/public/uploads/ 目录下
        $filepath = self::getBasePath() . $path;
        $info = $uploadFile->validate($config)->move($filepath, $savename, false);
        if (!$info) {
            return ResultVo::error(ErrorCode::DATA_NOT, $uploadFile->getError());
        }
        $filename = $info->getSaveName();
        $path = $path . $filename;
        $path = str_replace("\\", "/", $path);
        $fileExt = $info->getExtension();
        $className = isset(self::$extClaseArr[$fileExt]) ? self::$extClaseArr[$fileExt] : 'default-small';
        $res = array(
            "path" => $path,
            "filename" => $info->getFilename(),
            "className" => $className,
            'url' => PublicFileUtils::createUploadUrl($path),
            'mtime' => time(),
            "is_dir" => 0,
            "fileExt" => $fileExt,
            "size" => $info->getSize()
        );
        return ResultVo::success($res);
    }

    /*
     * 获取上传图片的列表
     */
    public function imageList()
    {
        $pathName = request()->get("pathName", "");
        $pathName = urldecode($pathName);
        $pathName = trim($pathName, '/'); // 去掉最前或者最后的 /
        $pathName = trim($pathName, '\\'); // 去掉最前或者最后的 \
        $pathName = self::$RESOURCES_PATH . $pathName;
        $pathName = trim($pathName, '/'); // 去掉最前或者最后的 /
        $pathName = trim($pathName, '\\'); // 去掉最前或者最后的 \
        $baseUrl = PublicFileUtils::createUploadUrl();
        /* 获取参数 */
        $size = request()->get('size/d', 20);
        $page = request()->get('page/d', 1);
        $basePath = self::getBasePath();

        // 检查资源文件是否存在
        if (!self::checkPath($basePath)) {
            return ResultVo::error(ErrorCode::DATA_NOT, "目录不存在");
        }

        /* 获取文件列表 */
        $files = self::getFiles($basePath, $pathName, $baseUrl);
        /* 获取指定范围的列表 */
        $len = count($files);
        $page = $page <= 0 ? 1 : $page;
        $countpage = ceil($len / $size); // 计算总页面数
        $page = $page > $countpage ? $countpage : $page;
        $start = $page * $size - $size;
        $end = $start + $size;
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--) {
            $list[] = $files[$i];
        }
        $res = [];
        $res['total'] = $len;
        $res['list'] = $list;
        $res['pathName'] = $pathName;
        return ResultVo::success($res);
    }


    /**
     * 获取目录下的文件/文件夹
     * @param $basePath
     * @param $pathName
     * @param $baseUrl
     * @return array|null
     */
    private static function getFiles($basePath, $pathName, $baseUrl)
    {
        $path = $basePath . $pathName;
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        $files = [];
        while (false !== ($filename = readdir($handle))) {
            if ($filename != '.' && $filename != '..') {
                $path2 = $path . $filename;
                $is_dir = is_dir($path2);
                $className = "dir-small";
                $fileExt = '';
                $mtime = file_exists($path2) ? filemtime($path2) : 0;
                $size = !$is_dir && file_exists($path2) ? filesize($path2) : 0;
                if (!$is_dir) {
                    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $className = isset(self::$extClaseArr[$fileExt]) ? self::$extClaseArr[$fileExt] : 'default-small';
                }
                $path3 = $pathName . '/' . $filename;
                $path3 = trim($path3, DIRECTORY_SEPARATOR);
                $path3 = str_replace('\\', "/", $path3);
                $url = $baseUrl . $path3;
                $files[] = array(
                    "path" => $path3,
                    "filename" => $filename,
                    "className" => $className,
                    'url' => $url,
                    'mtime' => $mtime,
                    "is_dir" => $is_dir ? 1 : 0,
                    "fileExt" => $fileExt,
                    "size" => $size
                );
            }
        }
        return $files;
    }

    /**
     * 检查目录是否可写
     * @access protected
     * @param  string $path 目录
     * @return boolean
     */
    private static function checkPath($path)
    {
        if (is_dir($path)) {
            return true;
        }
        if (mkdir($path, 0755, true)) {
            return true;
        }
        return false;
    }

}