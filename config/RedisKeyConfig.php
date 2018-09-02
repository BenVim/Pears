<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/1
 * Time: 15:56
 * Desc: redis key的配置表。系统会根据配置自动解析成对应的redis key
 */

namespace config;


class RedisKeyConfig
{
    const REDIS_KEY_LOG = "pears:{uid}:";
    const REDIS_KEY_ONLINE = "REDIS_KEY_ONLINE";

}