<?php


namespace Ybren\Codis;


interface LockInterface
{
    /**
     * 获得分布式锁
     * @param $key
     * @param $value
     * @return bool
     */
    public static function acquireLock($key, $value);

    /**
     * 释放分布式锁
     * @return mixed
     */
    public static function releaseLock();
}