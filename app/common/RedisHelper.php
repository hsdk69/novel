<?php


namespace app\common;


use think\facade\Env;

class RedisHelper
{
    public static function GetInstance()
    {
        $redis = app('redis');
        $redis->connect(Env::get('cache.hostname'), Env::get('cache.port'));
        if (!empty(Env::get('cache.password'))) {
            $redis->auth(Env::get('cache.password'));
        }
        return $redis;
    }
}