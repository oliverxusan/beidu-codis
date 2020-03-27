<?php


namespace Ybren\Codis\Config;

/**
 * 枚举值Connect.host = '127.0.0.1:6379'
 * 枚举值Connect.password = 'redis密码'
 * 枚举值Connect.select = 0
 * 枚举值Connect.timeout = 3
 * 枚举值Connect.expire = 3600
 * 枚举值Connect.prefix = ''
 *
 * Class RedisConf
 * @package Ybren\Codis\Config
 */
class RedisConf
{
    private $host;

    private $password;

    private $select;

    private $timeout;

    private $expire;

    private $prefix;

    private $failOverFlag;

    /**
     * @return mixed
     */
    public function getFailOverFlag()
    {
        return $this->failOverFlag;
    }

    /**
     * @param mixed $failOverFlag
     */
    public function setFailOverFlag($failOverFlag)
    {
        $this->failOverFlag = $failOverFlag;
    }
    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @param mixed $select
     */
    public function setSelect($select): void
    {
        $this->select = $select;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @param mixed $expire
     */
    public function setExpire($expire): void
    {
        $this->expire = $expire;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return mixed
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getExpire()
    {
        return $this->expire;
    }
}