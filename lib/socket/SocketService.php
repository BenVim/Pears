<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/3
 * Time: 00:17
 */

namespace lib\core;


use config\RedisKeyConfig;
use src\factory\CommandFactoryService;

class SocketService
{
    private $server;
    private $db;
    private $redis;

    function __construct()
    {
        $this->server = new SwooleWebSocket($this);
    }

    public function startService()
    {
        $this->server->start();
    }


    public function onWorkerStart($server, $worker_id)
    {
        $server->redis = RedisDBService::getInstance();
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
                $task_id = $server->task(json_encode($data));
            }
        }
    }

    public function onTask($server, $task_id, $frame, $data)
    {
        dump("task_id:", $task_id);
        $this->route($server, $data);
    }

    public function onFinish($server, $task_id, $data)
    {
        //TODO onFinish()
    }

    public function onClose($server, $fd, $reactorId)
    {
        $redis     = $server->redis;
        $onlineKey = RedisKeyService::getRedisKey(RedisKeyConfig::REDIS_KEY_ONLINE);
        $redis->sRemove($onlineKey, $fd);
    }

    public function route($server, $respond)
    {
        $result = @json_decode($respond, true);
        $data   = json_decode($result["data"], true);
        if (!isset($data['t'])) {
            dump('no t param');//TODO 修改成错误返回给客户端。
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
            $this->initCommand($obj, $data);
        }
    }

    private function initCommand(Command $obj, array $data)
    {
        $obj->init($data);
        $obj->receiveCommand();
        unset($obj);
    }


}