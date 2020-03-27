<?php


namespace Ybren\Codis\Connection;


use Ybren\Codis\Config\CodisConf;
use Ybren\Codis\Config\RedisConf;
use Ybren\Codis\Enum\ConnEnum;
use Ybren\Codis\Exception\CodisException as CodisExceptionAlias;
use Ybren\Codis\Exception\CodisException;
use Ybren\Codis\Exception\ConnException;
use Ybren\Codis\Zookeeper\RedisFromZk;

/**
 * 连接类型
 * Class Conn
 * @package Ybren\Codis\Connection
 */
class Conn implements ConnInterface
{

    private $connType = null;

    private $configObject = [];

    public function __construct(ConnEnum $connObj)
    {
        if(!class_exists("\\think\\Config")){
            throw new CodisException("So Far. Only Adapter one for Thinkphp when get config file.");
        }
        foreach ($connObj::toArray() as $key=>$value){
            $config = \think\Config::iniGet(strtolower($value).'Connect');
            !empty($config) && $this->configObject[strtoupper($value)] = $this->initConfigure($config,strtoupper($value));
        }
        $this->connType = $connObj->getValue();
    }

    /**
     * 获取连接类型
     * @return mixed
     */
    public function getConnType()
    {
        return $this->connType;
    }

    /**
     * 通过匿名函数获取连接句柄
     * @param callable $callback
     * @return object
     */
    public function getSock($callback)
    {
        return $callback($this->configObject);
    }


    /**
     * 初始化配置文件
     * @param array $conf
     * @param object $connType
     * @return object
     * @throws ConnException
     */
    public function initConfigure($conf, $connType)
    {
        if (empty($conf)){
            throw new ConnException("config set is nil");
        }
        if ($connType == strtoupper((string)ConnEnum::YBRCLOUD())){
            $f = new CodisConf();
            $f->setPassword($conf['password']);
            $f->setPrefix($conf['prefix']);
            !$conf['select'] ? $f->setSelect(0) : $f->setSelect($conf['select']);
            !$conf['expire'] ? $f->setExpire(86400) : $f->setExpire($conf['expire']);
            !$conf['timeout'] ? $f->setTimeout(3) : $f->setTimeout($conf['timeout']);
            if (!isset($conf['zkHost']) || empty($conf['zkHost'])){
                throw new ConnException("Select Codis Connection type. zkHost field is require.");
            }
            $f->setZkHost($conf['zkHost']);
            $f->setZkPassword($conf['zkPassword']);
            !$conf['retryTime'] ? $f->setRetryTime(3) : $f->setRetryTime($conf['retryTime']);
            $f->setZkName($conf['zkName']);
            return $f;
        }else{
            $f = new RedisConf();
            $f->setHost($conf['host']);
            $f->setPassword($conf['password']);
            $f->setPrefix($conf['prefix']);
            !$conf['select'] ? $f->setSelect(0) : $f->setSelect($conf['select']);
            !$conf['expire'] ? $f->setExpire(86400) : $f->setExpire($conf['expire']);
            !$conf['timeout'] ? $f->setTimeout(3) : $f->setTimeout($conf['timeout']);
            return $f;
        }
    }

    /**
     * 通过分配的连接方式获取句柄
     * @return mixed
     * @throws ConnException
     * @throws CodisExceptionAlias
     */
    public function getAssignSock()
    {
        $sock = null;
        //获取当前设置连接源
        if (isset($this->configObject[$this->getConnType()])){
            $config = $this->configObject[$this->getConnType()];
            if ($this->getConnType() == strtoupper((string)ConnEnum::YBRCLOUD())){
                $sock = $this->initCodis($config);
            }else{
                $sock = $this->initRedis($config);
            }
        }else{
            throw new ConnException( "Constant " . $this->getConnType() . " in Enum Class, Config Information is blank.");
        }
        if ($sock){
            return $sock;
        }
        throw new ConnException("Connection is wrong!!");
    }

    /**
     * 初始化codis
     * @param CodisConf $conf
     * @return \Redis
     * @throws CodisExceptionAlias
     */
    public function initCodis(CodisConf $conf)
    {
        try {
            $sock = RedisFromZk::connection($conf);
            if ($conf->getPassword()) {
                $sock->auth($conf->getPassword());
            }
            if ($conf->getSelect() > 0) {
                $sock->select($conf->getSelect());
            }
            return $sock;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 初始化 redis
     * @param RedisConf $conf
     * @return \Redis
     */
    public function initRedis(RedisConf $conf)
    {
        try {
            $sock = new \Redis();
            if (strstr($conf->getHost(), ":")) {
                list($host, $port) = explode(":", $conf->getHost());
                $sock->connect($host, $port, $conf->getTimeout());
            }
            if ($conf->getPassword()) {
                $sock->auth($conf->getPassword());
            }
            if ($conf->getSelect() > 0) {
                $sock->select($conf->getSelect());
            }
            return $sock;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 获取config对象
     * @return mixed
     */
    public function getConfObj()
    {
        return isset($this->configObject[$this->getConnType()]) ? $this->configObject[$this->getConnType()] : null;
    }
}