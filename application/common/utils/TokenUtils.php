<?php
/**
 * Created by PhpStorm.
 * User: Host-0034
 * Date: 2018/7/20
 * Time: 15:12
 */

namespace app\common\utils;


/*
 * token 生成类
 */

class TokenUtils
{

    /**
     * token 生成
     * @param $v string 生成的value 值
     * @return mixed
     */
    public static function create($v)
    {
        $key = mt_rand();
        $hash = hash_hmac("sha1", $v . mt_rand() . time(), $key, true);
        $token = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
        return $token;
    }

}