<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/2
 * Time: 23:26
 */

namespace src\factory;


use config\RedisKeyConfig;
use lib\core\RedisDBService;
use lib\core\RedisKeyService;

class RedisService
{
    public static function resetRedisData()
    {
        self::cleanRedisData(RedisKeyService::getRedisKey(RedisKeyConfig::REDIS_KEY_LOG, 0));
    }

    //清理redis数据
    private static function cleanRedisData($redisKey){

        $key = RedisKeyService::getRedisKey($redisKey, "*");
        $keyList = RedisDBService::getInstance()->getKeys($key);
        foreach ($keyList as $key) {
            RedisDBService::getInstance()->delete($key);
        }
    }
}