<?php

use lib\core\Config;

/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/3
 * Time: 00:52
 */

function dump(...$args)
{
    if (!Config::get('debug')) {
        return false;
    }

    $len = count($args);
    if ($len == 0) {
        return "";
    }

    $str = "";
    for ($index = 0; $index < $len; $index++) {
        $data = $args[$index];
        if(is_array($data)){
            $data = json_encode($data);
        }
        if ($index < $len - 1)
            $str .= $data. " ";
        else
            $str .= $data;
    }
    $showtime = date("Y-m-d H:i:s");
    $content  = "[ $showtime ] " . $str . "\n";
    print_r($content);
    return true;
}

function getParam($key, $array, $default = 0)
{
    $value = $default;
    if (is_array($array) && array_key_exists($key, $array)) {
        $value = $array[$key];
    }
    return $value;
}