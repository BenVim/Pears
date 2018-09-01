<?php
/*
 * @Author: Ben
 * @Date: 2017-08-09 15:50:31
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-24 11:26:19
 * Game Server主程序
 */

namespace src;

use lib\core\BaseObject;
use lib\core\Config;
use lib\core\Log;
use lib\core\PDORepository;
use lib\core\SwooleWebSocket;
use src\command\TickCommand;
use src\factory\CommandFactory;
use src\factory\CommandType;
use src\models\PlayerModel;
use src\models\UserModel;
use src\platform\GuildManager;
use src\util\RedisKeyList;
use src\util\RedisUtility;

class MainApp
{

    private $server;
    private $db;
    private $redisKeyList;
    private $redis;

    function __construct()
    {


        $this->registerCommand();
    }

    private function registerCommand()
    {
        CommandFactory::init();
    }

    public function init()
    {
        $path = \getcwd() . '/..env';
        Config::loadConfigurationFiles($path);
        $this->initData();
        $this->server = new SwooleWebSocket(Config::get('server.ip'), Config::get('server.port'), $this);
        $this->server->start();
    }

    public function initData()
    {
        //清理在线人数记录
        $this->redis        = new RedisUtility();
        $this->redisKeyList = new RedisKeyList();
        $key                = $this->redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_ONLINE);
        $this->redis->delete($key);
        $this->clearData(RedisKeyList::REDIS_KEY_USER_FD);
        $this->clearData(RedisKeyList::REDIS_KEY_UNION_LIST);
        $this->clearData(RedisKeyList::REDIS_KEY_LOGIN_CLIENT_INFO);//清理redis 用户登录的数据。
        $this->clearData(RedisKeyList::REDIS_KEY_USER_HOUSE);
        $this->clearData(RedisKeyList::REDIS_KEY_UNION_RULE_KEY);
        $this->clearData(RedisKeyList::REDIS_KEY_UNION_USER_KEY);
        $this->clearData(RedisKeyList::REDIS_KEY_UNION_CURRENT_KEY);
    }

    private function clearData($k)
    {
        $key     = $this->redisKeyList->getRedisKey($k, "*");
        $keyList = $this->redis->getKeys($key);
        foreach ($keyList as $key) {
            $this->redis->delete($key);
        }
    }

    public function onWorkerStart($server, $worker_id)
    {
        dump('onWorkerStart');
        $redis         = new RedisUtility();
        $server->redis = $redis;
        $db            = PDORepository::getInstance();
        $server->db    = $db;
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

        $obj = CommandFactory::getInstance($data['t']);

        if ($obj) {
            $obj->init($data);
            $obj->receiveCommand();
            unset($obj);
        }
    }
}
