<?php
/**
 * Created by PhpStorm.
 * User: Host-0034
 * Date: 2018/7/20
 * Time: 15:36
 */

namespace app\common\utils;


/*
 * 公共文件的处理工具类
 */
class PublicFileUtils
{

    /**
     * 生成上传文件的url
     * @param string $file_path 文件path
     * @param string $bucket 空间名称
     * @return string
     */
    public static function createUploadUrl($file_path = '', $bucket = '')
    {
        if(strpos($file_path,"http") === 0){
            return $file_path;
        }else if(strpos($file_path,"/") === 0){
            return $file_path;
        }
        $bucket = $bucket ? $bucket : "default";
        $url = config('public_file.' . $bucket . '.domain') . '/' . $file_path;

        return $url;
    }

}