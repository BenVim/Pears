<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/1
 * Time: 15:50
 * desc: redis key 自动生成的方法
 */


namespace lib\core;


class RedisKeyContainer
{
    public static function getRedisKey(...$args)
    {
        $len = count($args);
        if ($len == 0) {
            return "";
        }

        $keyTemplate = $args[0];
        if ($len == 1)
            return $keyTemplate;

        preg_match_all("/{[A-Z_]+}/", $keyTemplate, $matches, PREG_PATTERN_ORDER);

        if (count($matches[0]) == count($args) - 1) {
            foreach ($matches[0] as $key => $match) {
                $keyTemplate = str_replace($match, $args[$key + 1], $keyTemplate);
            }
        } else {
            echo "args num is error";
            return "";
        }
        return $keyTemplate;
    }

}