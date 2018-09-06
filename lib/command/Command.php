<?php

namespace lib\command;

/*
 * @Author: Ben 
 * @Date: 2017-08-09 15:25:46 
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-18 15:48:25
 */
use src\redisModels\UserRedisModel;
use src\util\RedisKeyList;
use src\util\RedisUtility;
use src\models\UserModel;

use src\models\PlayerModel;
use src\error\ErrorException;

class Command extends BaseObject
{
    protected $originData;
    protected $server;
    protected $fd;
    protected $data;
    protected $db;
    protected $info;
    protected $uid;
    protected $id;
    protected $houseId;
    protected $isVerify;
    protected $userData;
    protected $commandType;
    protected $unverifyMessageType = ['chat', 'login', 'heart', 'verify', 'error', 'online', 'state']; //不确认的消息
    protected $playerModel;
    protected $houseModel;
    protected $redis;
    protected $logModel;
    protected $userModel;
    protected $redisKeyList;
    protected $isQuickClick;


    public function __construct()
    {
        $this->redisKeyList = new RedisKeyList();
        $this->isQuickClick = false;
    }

    public function delegate($db)
    {
        $this->db = $db;

    }

    public function init($data, $isVerify = false)
    {
        $this->originData  = $data;
        $this->server      = $data['server'];
        $this->fd          = $data['fd'];
        $this->data        = isset($data['d']) ? $data['d'] : null;
        $this->info        = @$data['server']->connection_info($data['fd']);
        $this->id          = isset($data['id']) ? $data['id'] : null;
        $this->uid         = @$this->info['uid'];
        $this->isVerify    = $isVerify;
        $this->commandType = $data['t'];
        $this->db          = $data['db'];
        $this->redis       = $data['redis'];

        if ($isVerify) {
            if ($this->verifyMessage($data)) {
                //$this->receiveCommand();
            }
        } else {
            //$this->receiveCommand();
        }
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    //验证消息 //检查消息是否已存在，如果已存在则忽略。如果不存在，则添加入reids中去，并且发送确认消息。
    //redis 消息保存key结构 verifMessage:userid:time 1;
    private function verifyMessage($data)
    {

        if ($this->info) {
            $mesageType = $data['t'];
            $uid        = $this->uid;
            $time       = isset($data['id']) ? $data['id'] : 0;
            if ($time) {
                if (!in_array($mesageType, $this->unverifyMessageType)) {
                    $redis = new RedisUtility();
                    $key   = "verifyMessage:$uid:$time";
                    if (!$redis->exists($key)) {
                        $redis->setValue("verifyMessage:$uid:$time", $time);
                        $postData['t'] = 'verify';
                        $postData['d'] = ['id' => $time];
                        $this->pushData($postData);
                        return true;
                    } else {

                        return false;//忽略数据
                    }
                } else {
                    return true;
                }
            }
        }
        return true;
    }

    //接收命令
    public function receiveCommand()
    {
        $this->userModel = new UserModel($this->db);
        $this->userData  = $this->userModel->getCurrentUserDataWithUID($this->uid);
//        $userRedisModel = new UserRedisModel($this->redis);
//        $this->userData = $userRedisModel->getUser($this->fd);
        $this->logicalProcessing();
    }

    //逻辑处理
    protected function logicalProcessing()
    {

    }

    //格式化数据
    protected function formattedData()
    {

    }

    //发送数据
    protected function pushData($postData)
    {
        $this->pushDataWidthFd($this->fd, $postData);
    }

    protected function pushDataWidthFd($fd, $postData)
    {
        if ($this->server->connection_info($fd)) {
            $this->server->push($fd, json_encode($postData, JSON_UNESCAPED_UNICODE));
        }else{
            //dump("pushDataWidthFd:",$fd);
        }
    }

    protected function pushDataOntime($fd, $postData, $time)
    {
        $server = $this->server;
        $this->server->tick(2000, function ($id) use ($server, $fd, $postData, $time) {
            $key   = "ServerMessageVerify:$time";
            $redis = new RedisUtility();
            if ($redis->getValue($key)) {
                if ($server->connection_info($fd)) {
                    $server->push($fd, json_encode($postData, JSON_UNESCAPED_UNICODE));
                } else {
                    $server->clearTimer($id);//断线了，停止发送
                    $redis->delete($key);
                }
            } else {
                $server->clearTimer($id);//
            }
        });
    }

    //给房间所有人发消息 排除 exclude;
    public function pushAllUser($postData, $exclude = 0)
    {
        $playerModel = new PlayerModel($this->db);
        $userList    = $playerModel->getAllPlayers($this->uid);
        $this->pushUser($userList, $postData, $exclude);
    }

    public function pushAllUserWidthRoomID($houseId, $postData)
    {
        $userList = $this->getAllUserDataWidthRoomID($houseId);
        $this->pushUser($userList, $postData);
    }

    public function pushUser($userList, $postData, $exclude = 0)
    {
        if ($postData['t'] != "heart" && Config::get('debug')) {
            $uid = $this->uid;
            //dump("server post to client[$uid]:", json_encode($postData));
        }

        if ($userList) {
            foreach ($userList as $key => $value) {
                if ($this->server->connection_info($value['fd'])) {
                    if ($value['fd'] != $exclude) {
                        $this->pushDataWidthFd($value['fd'], $postData);
                    }
                }
            }
        }
    }


    public function pushErrorData($errorCode)
    {
        $errorObj = new ErrorException();
        $postData = $errorObj->getErrorPostData($errorCode);
        $this->pushData($postData);
    }

    public function pushCustomErrorData($postData)
    {
        $this->pushData($postData);
    }


    protected function checkRoomState($roomData, $gameState)
    {
        $key   = $this->redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_GAME_STATE, $roomData['id']);
        $state = $this->redis->getValue($key);

        if ($state == $gameState) {
            return true;
        }
        return false;
    }

    protected function operateLock($uid)
    {
        $key = $this->redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_USER_OPERATE_LOCK, $uid);
        if ($this->redis->exists($key)) {
            $time = $this->redis->getValue($key);
            if (time() - $time < 2) {
                $this->pushErrorData(ErrorException::ERROR_QUICK_OPERATE);
                return false;
            }
        }
        $this->redis->setValue($key, time());
        return true;
    }

    protected function saveRuleToRedis($rule, $unionId)
    {
        $key = $this->redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_UNION_RULE_KEY, $unionId);
        $this->redis->setValue($key, json_encode($rule, JSON_UNESCAPED_UNICODE));
    }

    protected function getRuleForRedis($unionId){
        $key = $this->redisKeyList->getRedisKey(RedisKeyList::REDIS_KEY_UNION_RULE_KEY, $unionId);
        return $this->redis->getValue($key);
    }
}