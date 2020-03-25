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
            $this->_instance = Codis::getInstance();
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
        $bool = $this->_instance->setNx($key,$clientId);
        if ($bool) {
            $this->_instance->expire($key,$ttl);
            return true;
        }
        return false;
    }

    /**
     * 释放分布式锁
     * @param $key
     * @param $clientId
     * @return mixed
     */
    public function releaseLock($key, $clientId)
    {
        return $this->_instance->handler()->eval(static::SCRIPT, [$key, $clientId], 1);
    }
}