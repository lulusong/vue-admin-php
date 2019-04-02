<?php
/**
 * Created by PhpStorm.
 * User: Host-0034
 * Date: 2018/7/20
 * Time: 15:18
 */

namespace app\common\utils;


use app\common\exception\JsonException;
use think\facade\Cache;

/*
 * redis  操作工具类
 */

class RedisUtils
{

    /**
     * @param string $store
     * @return \Redis
     * @throws JsonException
     */
    public static function init($store = "default")
    {
        $redis = Cache::store($store)->handler();
        // 判断缓存类是否为 redis
        if ($redis instanceof \Redis){
            return $redis;
        }
        throw new JsonException(1, "Redis link timeout");
    }

}