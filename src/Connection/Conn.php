<?php


namespace Ybren\Codis\Connection;


use Ybren\Codis\Config\Conf;
use Ybren\Codis\Enum\BizEnum;
use Ybren\Codis\Enum\ConnEnum;
use Ybren\Codis\Exception\CodisException as CodisExceptionAlias;
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

    private $retry = 3;

    private $refCount = 0;

    private $configObject = null;

    public function __construct($config = [])
    {
        $this->configObject = $this->initConfigure($config);
    }

    /**
     * 设置连接类型
     * @param ConnEnum $type
     * @return mixed
     * @throws ConnException
     */
    public function setConnType($type)
    {
        $this->verifyConnType(strtoupper($type));
        $this->connType = $type;
        $this->configObject->setConnType(strtoupper($type));
        $this->switchVerify($this->configObject);
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
     * @param array $conf
     * @param callable $callback
     * @return object
     */
    public function getSock($conf, $callback)
    {
        return $callback($this->configObject);
    }


    /**
     * 初始化配置文件
     * @param array $conf
     * @return object
     * @throws ConnException
     */
    public function initConfigure($conf)
    {
        if (empty($conf)){
            throw new ConnException("config set is nil");
        }
        if (!extension_loaded('redis')) {
            throw new ConnException('not support: redis');
        }
        $f = new Conf();
        $this->verifyConnType($conf['connType']);
        $f->setConnType($conf['connType']);
        $f->setAliHost($conf['aliHost']);
        $f->setAliPwd($conf['aliPwd']);
        $f->setLocalHost($conf['localHost']);
        $f->setLocalPwd($conf['localPwd']);

        $f->setPassword($conf['password']);
        $f->setPrefix($conf['prefix']);
        !$conf['select'] ? $f->setSelect(0) : $f->setSelect($conf['select']);
        !$conf['expire'] ? $f->setExpire(86400) : $f->setExpire($conf['expire']);
        !$conf['timeOut'] ? $f->setTimeout(3) : $f->setTimeout($conf['timeOut']);

        //zookeeper配置
        if (strtoupper($conf['connType']) == ConnEnum::YBRCLOUD){
            if (!isset($conf['zkHost']) || empty($conf['zkHost'])){
                throw new ConnException("Select Codis Connection type. zkHost field is require.");
            }
            $f->setZkHost($conf['zkHost']);
        }
        $f->setZkPassword($conf['zkPassword']);
        !$conf['retryTime'] ? $f->setRetryTime(3) : $f->setRetryTime($conf['retryTime']);
        $f->setZkName($conf['zkName']);
        return $f;
    }

    /**
     * 通过分配的连接方式获取句柄
     * @return mixed
     * @throws ConnException
     * @throws CodisExceptionAlias
     */
    public function getAssignSock()
    {
        if ($this->refCount >= 3){
            throw new ConnException("The number of attempts has overflowed.");
        }

        $confObj = $this->configObject;
        switch (strtoupper($confObj->getConnType())){
            case ConnEnum::YBRCLOUD:
                $sock = $this->initCodis($confObj);
                if (!$sock && $this->refCount <= $this->retry){
                    $confObj->setConnType(ConnEnum::ALICLOUD);
                    $this->refCount++;
                    return $this->getAssignSock();
                }
                return $sock;
            case ConnEnum::ALICLOUD:
                $sock = $this->initAliRedis($confObj);
                if (!$sock && $this->refCount <= $this->retry){
                    $confObj->setConnType(ConnEnum::LOCAL);
                    $this->refCount++;
                    return $this->getAssignSock();
                }
                return $sock;
            case ConnEnum::LOCAL:
                $sock = $this->initLocal($confObj);
                if (!$sock && $this->refCount <= $this->retry){
                    $confObj->setConnType(ConnEnum::YBRCLOUD);
                    $this->refCount++;
                    return $this->getAssignSock();
                }
                return $sock;
            default:
                return $this->initCodis($confObj);
        }
    }

    /**
     * 初始化codis
     * @param Conf $conf
     * @return \Redis
     * @throws CodisExceptionAlias
     */
    public function initCodis(Conf $conf)
    {
        try {
            $sock = RedisFromZk::connection($conf);
            if (!$conf->getPrefix()) {
                $conf->setPrefix(BizEnum::NORMAL);
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
     * 初始化ali redis
     * @param Conf $conf
     * @return \Redis
     */
    public function initAliRedis(Conf $conf)
    {
        try {
            $sock = new \Redis();
            if (strstr($conf->getAliHost(), ":")) {
                list($host, $port) = explode(":", $conf->getAliHost());
                $sock->connect($host, $port, $conf->getTimeout());
            }
            if ($conf->getAliPwd()) {
                $sock->auth($conf->getAliPwd());
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
     * 初始化本地 redis
     * @param Conf $conf
     * @return \Redis
     */
    public function initLocal(Conf $conf)
    {
        try {
            $sock = new \Redis();
            if (strstr($conf->getLocalHost(), ":")) {
                list($host, $port) = explode(":", $conf->getLocalHost());
                $sock->connect($host, $port, $conf->getTimeout());
            }
            if ($conf->getLocalPwd()) {
                $sock->auth($conf->getLocalPwd());
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
        return $this->configObject;
    }

    /**
     * 验证连接类型
     * @param $connType
     * @return void
     * @throws ConnException
     */
    private function verifyConnType($connType)
    {
        if (!in_array($connType,[ConnEnum::ALICLOUD,ConnEnum::LOCAL,ConnEnum::YBRCLOUD])){
            throw new ConnException("Unknow Connection Type.");
        }
    }

    /**
     * 验证类型数据是否正确
     * @param Conf $f
     * @throws ConnException
     */
    private function switchVerify(Conf $f){
        // redis ali配置
        if (strtoupper($f->getConnType()) == ConnEnum::ALICLOUD){
            if (!$f->getAliHost()){
                throw new ConnException("Select one Connection type which ALICLOUD . aliHost field is require.");
            }
            if (!strstr($f->getAliHost(),":")){
                throw new ConnException("Please set ALI redis port");
            }
        }
        // redis 本地配置
        if (strtoupper($f->getConnType()) == ConnEnum::LOCAL){
            if (!$f->getLocalHost()){
                throw new ConnException("Select one Connection type which LOCAL. localHost field is require.");
            }
            if (!strstr($f->getLocalHost(),":")){
                throw new ConnException("Please set Local redis port");
            }
        }
    }
}