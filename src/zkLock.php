<?php
namespace Ybren\Codis;


use Ybren\Codis\Zookeeper\ZkDistributedLock;

/**
 * zookeeper分布式锁
 * Class Lock
 * @package Ybren\Codis
 */
class zkLock implements LockInterface
{
    /**
     * 操作对象实例
     * @var Cmd|null
     */
    private $_instance = null;


    public function __construct($config = array())
    {
        if (empty($this->_instance)){
            //默认读tp中yaconf配置文件
            $this->_instance = new ZkDistributedLock($config,"/locks/");
        }
    }

    /**
     * 获得分布式锁
     * @param $key
     * @param $clientId
     * @param $ttl 不使用
     * @return bool
     */
    public function acquireLock($key, $clientId, $ttl = -1)
    {
        $this->_instance->tryGetDistributedLock($key,$clientId);
    }

    /**
     * zookeeper 释放分布式锁 不需要传key 和 clientId
     * @param $key
     * @param $clientId
     * @return mixed
     */
    public function releaseLock($key = '', $clientId = '')
    {
        $this->_instance->releaseDistributedLock();
    }
}