<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 18/10/2017
 * Time: 13:44
 */

namespace lib\core;


use src\util\RedisKeyList;
use src\util\RedisUtility;

class RedisModel
{

    protected $redisKeyList;
    protected $redis;

    public function __construct($redis)
    {
        $this->redisKeyList = new RedisKeyList();
        $this->redis = $redis;
    }

    public function deleteKey($key){
        $this->redis->delete($key);
    }




}