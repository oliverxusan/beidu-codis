<?php


namespace Ybren\Codis;


/**
 * redis分布式锁
 * Class rdLock
 * @package Ybren\Codis
 */
class rdLock implements LockInterface
{
    /**
     * LUA 脚本
     */
    const SCRIPT = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

    /**
     * 操作对象实例
     * @var Cmd|null
     */
    private $_instance = null;

    public function __construct($config = array())
    {
        if (empty($this->_instance)){
            //默认读tp中yaconf配置文件
            $this->_instance = Codis::handler();
        }
    }


    /**
     * 获得分布式锁
     * @param $key
     * @param $clientId
     * @param $ttl
     * @return bool
     */
    public function acquireLock($key, $clientId, $ttl = 5)
    {
        return $this->_instance->set($key,$clientId, ['NX', 'EX'=>$ttl]);
    }

    /**
     * 释放分布式锁
     * @param $key
     * @param $clientId
     * @return mixed
     */
    public function releaseLock($key, $clientId)
    {
        return $this->_instance->eval(static::SCRIPT, [$key, $clientId], 1);
    }
}