<?php
/*
 * @Author: Ben
 * @Date: 2017-08-09 15:50:31
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-24 11:26:19
 * Game Server主程序
 */

namespace src;

use lib\core\Config;
use lib\core\SocketService;

class MainApp
{
    private $server;

    function __construct()
    {

    }

    public function init()
    {
        $path = \getcwd() . '/..env';
        Config::loadConfigurationFiles($path);

        $this->serviceStartBefore();
        $this->startService();
        $this->serviceStartAfter();
    }

    public function startService()
    {
        $this->server = new SocketService();
        $this->server->startService();
    }

    //启动服务之前
    private function serviceStartBefore(){

    }

    //启动服务之后
    private function serviceStartAfter(){

    }

    //服务停止的操作
    private function serviceStop(){

    }


}
