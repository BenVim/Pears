<?php

namespace lib\core;

/*
 * @Author: Ben 
 * @Date: 2017-08-09 15:41:44 
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-25 11:02:54
 */
class Config extends BaseObject
{
    public static $settings = [];
    public static $configPath = '';

    function __construct()
    {

    }

    public static function get($key)
    {
        return $_ENV[$key];
    }

    public static function loadConfigurationFiles($path)
    {
        $ini_array = parse_ini_file($path);
        $_ENV      = $ini_array;
        //print_r($ini_array);
        dump('config load success.');
    }

    function __destruct()
    {

    }
}