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
use src\factory\CommandFactoryService;
use src\factory\RedisService;
use src\factory\SocketService;

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
        //注册协议命令
        $this->server = new SocketService();
        $this->server->startService();
    }

    //启动服务之前
    private function serviceStartBefore(){
        RedisService::resetRedisData();
        CommandFactoryService::init();
    }

    //启动服务之后
    private function serviceStartAfter(){

    }

    //服务停止的操作
    private function serviceStop(){

    }


}
