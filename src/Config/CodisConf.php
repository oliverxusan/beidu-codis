<?php


namespace Ybren\Codis\Config;

/**
 * codisConnect.zkHost = '127.0.0.1:2181'
 * codisConnect.zkPassword = 'username:password'
 * codisConnect.zkName = 'codis项目名称'
 * codisConnect.zkTimeout = 5
 * codisConnect.retryTime = 3
 * codisConnect.password = 'redis密码'
 * codisConnect.select = 0
 * codisConnect.timeout = 3
 * codisConnect.expire = 3600
 * codisConnect.prefix = ''
 * 配置类
 * Class Conf
 * @package Ybren\Codis\Config
 */
class CodisConf
{
    /**
     * zookeeper ip:端号 地址 多个用逗号隔开
     * @var $zkHost
     */
    private $zkHost;

    /**
     * zookeeper 密码
     * @var $zkPassword
     */
    private $zkPassword;

    /**
     * zookeeper 项目名称
     * @var $zkName
     */
    private $zkName;

    /**
     * 重试次数
     * @var $retryTime
     */
    private $retryTime;

    /**
     * redis密码
     * @var $password
     */
    private $password;

    /**
     * redis数据库
     * @var $select
     */
    private $select;

    /**
     * redis连接超时时间
     * @var $timeout
     */
    private $timeout;

    /**
     * redis有效时间
     * @var $expire
     */
    private $expire;

    /**
     * 连接类型
     * @var $connType
     */
    private $connType;

    /**
     * redis 前缀
     * @var $prefix
     */
    private $prefix;

    /**
     * 连接枚举类
     * @var
     */
    private $connEnumClass;

    /**
     * 是否启用故障转移
     * @var
     */
    private $failOverEnable;

    /**
     * @return mixed
     */
    public function getFailOverEnable()
    {
        return $this->failOverEnable;
    }

    /**
     * @param mixed $failOverEnable
     */
    public function setFailOverEnable($failOverEnable)
    {
        $this->failOverEnable = $failOverEnable;
    }
    /**
     * @return mixed
     */
    public function getConnEnumClass()
    {
        return $this->connEnumClass;
    }

    /**
     * @param mixed $connEnumClass
     */
    public function setConnEnumClass($connEnumClass)
    {
        $this->connEnumClass = $connEnumClass;
    }
    /**
     * @return mixed
     */
    public function getConnType()
    {
        return $this->connType;
    }

    /**
     * @param mixed $connType
     */
    public function setConnType($connType)
    {
        $this->connType = $connType;
    }
    /**
     * @return mixed
     */
    public function getExpire()
    {
        return $this->expire;
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
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return mixed
     */
    public function getRetryTime()
    {
        return $this->retryTime;
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
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return mixed
     */
    public function getZkHost()
    {
        return $this->zkHost;
    }

    /**
     * @return mixed
     */
    public function getZkName()
    {
        return $this->zkName;
    }

    /**
     * @return mixed
     */
    public function getZkPassword()
    {
        return $this->zkPassword;
    }

    /**
     * @param mixed $expire
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param mixed $retryTime
     */
    public function setRetryTime($retryTime)
    {
        $this->retryTime = $retryTime;
    }

    /**
     * @param mixed $select
     */
    public function setSelect($select)
    {
        $this->select = $select;
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param mixed $zkHost
     */
    public function setZkHost($zkHost)
    {
        $this->zkHost = $zkHost;
    }

    /**
     * @param mixed $zkName
     */
    public function setZkName($zkName)
    {
        $this->zkName = $zkName;
    }

    /**
     * @param mixed $zkPassword
     */
    public function setZkPassword($zkPassword)
    {
        $this->zkPassword = $zkPassword;
    }

}