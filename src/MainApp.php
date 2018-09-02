<?php
/*
 * @Author: Ben
 * @Date: 2017-08-09 15:50:31
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-24 11:26:19
 * Game Server主程序
 */

namespace src;

use config\Configuration;
use config\RedisKeyConfig;
use lib\core\Config;
use lib\core\Log;
use lib\core\PDORepository;
use lib\core\RedisDBService;
use lib\core\RedisKeyContainer;
use lib\core\SwooleWebSocket;
use src\factory\CommandFactoryService;
use src\factory\CommandType;
use src\factory\RedisService;

class MainApp
{

    private $server;
    private $db;
    private $redisKeyList;
    private $redis;

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
        $this->server = new SwooleWebSocket(Config::get('server.ip'), Config::get('server.port'), $this);
        $this->server->start();
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

    public function onWorkerStart($server, $worker_id)
    {
        $server->redis = new RedisService();
        $server->db    = PDORepository::getInstance();
    }

    public function onStart($server)
    {
        if (!Config::get('server.test')) {
            cli_set_process_title(Config::get('server.name'));
        }
    }

    public function onOpen($server, $frame)
    {
        Log::addLog('onOpen');
    }

    public function onMessage($server, $frame)
    {
        $opCode           = $frame->opcode;
        $finish           = $frame->finish;
        $messageData      = @json_decode($frame->data, true);
        $data             = array();
        $data['data']     = json_encode($messageData);
        $data['fd']       = $frame->fd;
        $data['workMain'] = $server;

        if ($opCode == WEBSOCKET_OPCODE_TEXT) {
            if ($finish) {
                if ($messageData['t'] == CommandType::GAME_TICK_COMMAND_KEY) {
                    $obj = new TickCommand($server);
                    $obj->init($data);
                    unset($obj);
                } else {
                    $task_id = $server->task(json_encode($data));
                }
            }
        }
    }

    public function onTask($server, $task_id, $frame, $data)
    {
        dump("task_id:", $task_id);
        //$data['task_id'] = $task_id;
        $this->route($server, $data);
    }

    public function onFinish($server, $task_id, $data)
    {
        //TODO onFinish()
    }

    public function onClose($server, $fd, $reactorId)
    {
        $redis        = $server->redis;//new RedisUtility();
        $redisKeyList = new RedisKeyList();

        $onlineKey = $redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_ONLINE);
        $redis->sRemove($onlineKey, $fd);
        dump("onClose", $fd);

        $info = $server->connection_info($fd);
        if ($info) {
            $uid = $info['uid'];
            if ($uid != 0) {
                $db          = $server->db;//PDORepository::getInstance();
                $playerModel = new PlayerModel($db);

                $key = $redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_USER_FD, $uid);
                if ($redis->getValue($key) == $fd) {
                    $oldFd = $redis->getSet($key, 0);
                    if ($oldFd != $fd) {
                        $redis->getSet($key, $oldFd);
                        $playerModel->updateFd($uid, $oldFd);
                    } else {
                        $playerModel->updateFd($uid, 0);
                    }
                }

                //清理公会列表的用户
                //找出用户对应的unionId.
                $unionId = 0;
                $obj     = new GuildManager($this->server, $unionId, $this->redis, $this->redisKeyList, 1);
                $obj->clearOnlineFd($uid, $fd);
            }
        }
    }

    public function route($server, $respond)
    {
        $result           = @json_decode($respond, true);
        $data             = json_decode($result["data"], true);//$result["data"];
        if (!isset($data['t'])) {
            dump('no t param');
            return;
        }
        $data['fd']       = $result['fd'];
        $data['server']   = $server;
        $data['workMain'] = $result['workMain'];
        $data['redis']    = $server->redis;
        $data['db']       = $server->db;
        $obj              = null;

        $obj = CommandFactoryService::getInstance($data['t']);

        if ($obj) {
            $obj->init($data);
            $obj->receiveCommand();
            unset($obj);
        }
    }
}
