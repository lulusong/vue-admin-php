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
    public static function createUploadUrl($file_path = '', $bucket = 'bucket')
    {
        if (empty($file_path)) {
            return "";
        }
        if(strpos($file_path,"http") === 0){
            return $file_path;
        }else if(strpos($file_path,"/") === 0){
            return $file_path;
        }
        $domain = self::getDomainBaseUrl();
        $url = $domain . '/' . $file_path;
        return $url;
    }

    /**
     * 获取上传的基础路径
     * @param string $bucket 空间名称
     * @return string
     */
    public static function getUploadBaseUrl()
    {
        $upload_url = config('public_file.bucket.upload_url');
        if (empty($domain)) {
            $upload_url = url("createFile", '', false, true);
        }
        return $upload_url;
    }

    /**
     * 获取公共文件的基础域名
     * @return string
     */
    public static function getDomainBaseUrl()
    {
        $domain = config('public_file.bucket.domain');
        if (empty($domain)) {
            $domain = url("/uploads/", "", false, true);
        }
        return $domain;
    }

}