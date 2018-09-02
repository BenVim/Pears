<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/2
 * Time: 23:26
 */

namespace src\factory;


class RedisService
{

    public static function resetRedisData()
    {

        self::cleanRedisData(RedisKeyContainer::getRedisKey(RedisKeyConfig::REDIS_KEY_LOG, 0));
    }

    //清理redis数据
    private static function cleanRedisData($redisKey){
        $key     = $this->redisKeyList->getRedisKey($redisKey, "*");
        $keyList = $this->redis->getKeys($key);
        foreach ($keyList as $key) {
            $this->redis->delete($key);
        }
    }
}