<?php

namespace lib\core;
use config\CommandTypeConfig;

/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 03/11/2017
 * Time: 17:13
 */
class CommandFactoryService
{
    public static $list = [];

    public static function init(){
        $commandInfoList = CommandTypeConfig::registerCommand();
        foreach ($commandInfoList as $key => $value){
            self::register($key, $value);
        }
    }

    public static function register($key, $className)
    {
        if (array_key_exists($key, self::$list)) {
            return false;
        }
        self::$list[$key] = $className;
    }

    public static function getInstance($key)
    {
        $obj = null;
        if (array_key_exists($key, self::$list)) {
            $obj = new self::$list[$key];
        }
        return $obj;
    }


}