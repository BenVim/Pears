<?php

namespace lib\core;

use Exception;
use Redis;
use lib\core\Config;

// 改造成单例
class RedisDB
{

    protected $redis;

    public function __construct()
    {
        $this->getConnection();
    }

    private function getConnection()
    {
        if ($this->redis) {
            return $this->redis;
        }
        try {
            $this->redis = new Redis();
            $this->redis->connect(Config::get('redis.ip'), Config::get('redis.port'));
            $this->redis->select(Config::get('redis.db'));

            //echo "Connection to server successfully";
            //check whether server is running or not 
            //echo "Server is running: ".$redis->ping(); 
        } catch (Exception $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
        return $this->redis;
    }

    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    public function sAdd($key, $value){
        return $this->redis->sAdd($key, $value);
    }

    public function sRemove($key, $value){
        return $this->redis->sRem($key, $value);
    }

    public function sMembers($key){
        return $this->redis->sMembers($key);
    }

    public function mget($key){
        return $this->redis->mget($key);
    }

    public function setValue($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    public function setValueNx($key, $value){
        return $this->redis->setnx($key, $value);
    }

    public function getSet($key, $value){
        return $this->redis->getSet($key, $value);
    }
    public function getValue($key)
    {
        return $this->redis->get($key);
    }

    public function gethmGet($key, $fields)
    {
        return $this->redis->hMget($key, $fields);
    }

    public function lPush($key, $value){
        return $this->redis->lPush($key, $value);
    }

    public function lPushx($key, $value){
        return $this->redis->lPushx($key, $value);
    }

    public function rPop($key){
        return $this->redis->rPop($key);
    }

    public function lSize($key){
        return $this->redis->lSize($key);
    }

    public function gethmAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    public function expireAt($key, $time)
    {
        return $this->redis->expireAt($key, $time);
    }

    public function setHmSet($key, $fields)
    {
        $this->redis->hMset($key, $fields);
    }

    public function delete($key)
    {
        $this->redis->delete($key);
    }

    //清空当前数据库
    public function flushDB()
    {
        $this->redis->flushDB();
    }

    public function save()
    {
        $this->redis->save(); //将数据同步保存到磁盘
    }

    public function bgsave()
    {
        $this->redis->bgsave();//将数据异步保存到磁盘
    }

    public function lastSave()
    {
        return $this->redis->lastSave();
    }

    //返回数据大小。
    public function dbSize()
    {
        $this->redis->dbSize();
    }

    //返回KEY
    public function getKeys($pattern)
    {
        $keyWithUserPrefix = $this->redis->keys($pattern);
        return $keyWithUserPrefix;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->redis->close();
    }


}