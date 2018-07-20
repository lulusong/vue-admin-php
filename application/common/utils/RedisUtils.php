<?php
/**
 * Created by PhpStorm.
 * User: Host-0034
 * Date: 2018/7/20
 * Time: 15:18
 */

namespace app\common\utils;


use think\facade\Cache;

/*
 * redis  操作工具类
 */

class RedisUtils
{

    /*
     * 获取 redis 实例
     */
    public static function init()
    {
        $redis = Cache::init()->handler();
        // 判断缓存类是否为 redis
        if ($redis instanceof \Redis){
            return $redis;
        }
        return false;
    }

}