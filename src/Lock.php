<?php
namespace Ybren\Codis;


use Ybren\Codis\Exception\LockException;
use Ybren\Codis\Zookeeper\ZkDistributedLock;

/**
 * 分布式锁
 * Class Lock
 * @package Ybren\Codis
 */
class Lock implements LockInterface
{

    /**
     * zookeeper
     * 获得分布式锁
     * @param $key
     * @param $value
     * @return bool
     */
    public static function acquireLock($key, $value){
        return ZkDistributedLock::tryGetDistributedLock($key, $value);
    }

    /**
     * 释放分布式锁
     * @return mixed
     */
    public static function releaseLock(){
        return ZkDistributedLock::releaseDistributedLock();
    }

    /**
     * 获得zk链接句柄
     * @param string $config
     * @return object
     * @throws \Exception
     */
    private static function connect($config = array()){
        return ZkDistributedLock::getZkInstance($config);
    }

    // 调用静态方法
    public static function __callStatic($method, $params){
        static::connect();
        return call_user_func_array(array(Lock::class, $method), $params);
    }
}