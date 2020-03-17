<?php


namespace Ybren\Codis;


interface LockInterface
{
    /**
     * 获得分布式锁
     * @param $key
     * @param $clientId
     * @param $ttl
     * @return bool
     */
    public function acquireLock($key, $clientId, $ttl);

    /**
     * 释放分布式锁
     * @param $key
     * @param $clientId
     * @return mixed
     */
    public function releaseLock($key, $clientId);

}