<?php

namespace lib\core;

/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 17/5/18
 * Time: 20:00
 */

use lib\core\Config;

class Log extends BaseObject
{
    public static function dump($content)
    {
        if (Config::get('debug')) {
            $showtime = date("Y-m-d H:i:s");
            $content  = "[ $showtime - dump]" . $content . "\n";
            print_r($content);
        }
    }

    public static function addLog($content)
    {
        if (Config::get('system.mysqlLog')) {
            $showtime = date("Y-m-d H:i:s");
            $content  = "[ $showtime - mysql]" . $content . "\n";
            print_r($content);
        }
    }

    public static function memoryLog($content)
    {
        if (Config::get('system.memoryLog')) {
            $showtime = date("Y-m-d H:i:s");
            $content  = "[ $showtime - memory]" . $content . "\n";
            $fileName = date('Y-m-d');
            print_r($content);
        }
    }

    public static function errorLog($content)
    {
        if (Config::get('system.errorLog')) {
            $showtime = date("Y-m-d H:i:s");
            $content  = "[ $showtime - error]" . $content . "\n";
            print_r($content);
        }
    }

    public static function efficiencyLog($content)
    {
        if (Config::get('system.efficiency')) {
            $showtime = date("Y-m-d H:i:s");
            $content  = "[ $showtime - efficiency]" . $content . "\n";
            print_r($content);
        }
    }

    public static function getConfig()
    {
        return true;
    }
}