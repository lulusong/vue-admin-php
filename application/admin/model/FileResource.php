<?php
// +----------------------------------------------------------------------
// | ThinkPHP 5 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 .
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 黎明晓 <lmxdawn@gmail.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\facade\Env;
use think\Model;

/**
 * 资源表
 */
class FileResource extends Model
{
    // 资源的根路径
    public static $RESOURCES_PATH = 'resources' . DIRECTORY_SEPARATOR;

    /**
     * 获取上传文件的根路径
     */
    public static function getBasePath()
    {
        return Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取url
     * @param $path
     * @return string
     */
    public static function getUrl($path){
        return "http://www.nihuawocai.com/uploads/" . $path;
        return get_asset_upload_path($path);
    }

    /*
     * 获取类型的path
     */
    public static function getTypePath($type = 0) {
        $types = [
            0 => 'image' . DIRECTORY_SEPARATOR
        ];
        return isset($types[$type]) ? $types[$type] : 'all';
    }

}
