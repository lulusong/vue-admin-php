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

namespace app\common\model\auth;

use app\common\constant\CacheKeyConstant;
use app\common\utils\RedisUtils;
use app\common\utils\TokenUtils;
use think\facade\Cache;
use think\Model;

/**
 * 管理员表
 */
class AuthAdmin extends Model
{

    /**
     * 获取登录信息
     * @param int $id 用户ID
     * @param array|string $values 如果这个值为数组则为设置用户信息，否则为 token
     * @param bool $is_login 是否验证用户是否登录
     * @return array|bool 成功返回用户信息，否则返回 false
     */
    public static function loginInfo($id, $values,$is_login = true){
        $redis = RedisUtils::init();
        $key = CacheKeyConstant::ADMIN_LOGIN_KEY . $id;
        // 判断缓存类是否为 redis
        if ($redis){
            if ($values && is_array($values)){
                $values['id'] = $id;
                $values['token'] = TokenUtils::create("admin" . $id);
                $values['authRules'] = isset($values['authRules']) ? json_encode($values['authRules']) : '';
                $res = $redis->hMset($key, $values);
                $values = $values['token'];
            }
            $info = $redis->hGetAll($key);
            if ($is_login === false){
                if (isset($info['token']))  unset($info['token']);
                return $info;
            }
            if (!empty($info['id']) && !empty($info['token']) && $info['token'] == $values){
                $info['authRules'] = isset($info['authRules']) ? json_decode($info['authRules']) : '';
                return $info;
            }
        }else{
            if ($values && is_array($values)){
                $values['id'] = $id;
                $values['token'] = TokenUtils::create("admin" . $id);
                $res = Cache::set($key, $values);
                $values = $values['token'];
            }
            $info = Cache::get($key);
            if ($is_login === false){
                if (isset($info['token']))  unset($info['token']);
                return $info;
            }
            if (!empty($info['id']) && !empty($info['token']) && $info['token'] == $values){
                return $info;
            }
        }


        return false;
    }

    /**
     * 退出登录
     * @return array|mixed
     */
    public static function loginOut($id){
        $redis = RedisUtils::init();
        $key = CacheKeyConstant::ADMIN_LOGIN_KEY . $id;
        // 判断缓存类是否为 redis
        if ($redis){
            $redis->del($key);
        }else{
            Cache::rm($key);
        }
    }

}
