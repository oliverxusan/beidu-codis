<?php


namespace Ybren\Codis\Connection;


use Ybren\Codis\Config\CodisConf;
use Ybren\Codis\Config\RedisConf;
use Ybren\Codis\Enum\ConnEnum;
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

    /**
     * 故障转移
     * @var bool
     */
    private $failOver = 0;// 是否启用 1启用 0禁用

    private $configObject = [];

    public function __construct($connObj)
    {
        if(!class_exists("\\think\\Config")){
            throw new ConnException("ERR So Far. Only Adapter one for Thinkphp when get config file.");
        }

        if (is_null($connObj)){
            $config = \think\Config::iniGet(strtolower(ConnEnum::YBRCLOUD()->getValue()).'Connect');
            $initObj = $this->configObject[strtoupper(ConnEnum::YBRCLOUD()->getValue())] = $this->initConfigure($config,strtoupper(ConnEnum::YBRCLOUD()->getValue()));
            //获取初始化枚举类
            $enumClass = $initObj->getConnEnumClass();
            foreach ($enumClass::toArray() as $key=>$value){
                if (!isset($this->configObject[strtoupper($value)])){
                    $config = \think\Config::iniGet(strtolower($value).'Connect');
                    !empty($config) && $this->configObject[strtoupper($value)] = $this->initConfigure($config,strtoupper($value));
                }
            }
            $this->failOver = $initObj->getFailOverEnable();
            $this->connType = empty($initObj->getConnType()) ? strtoupper(ConnEnum::YBRCLOUD()->getValue()) : strtoupper($initObj->getConnType());
        }else{
            foreach ($connObj::toArray() as $key=>$value){
                $config = \think\Config::iniGet(strtolower($value).'Connect');
                !empty($config) && $this->configObject[strtoupper($value)] = $this->initConfigure($config,strtoupper($value));
            }
            $initObj = isset($this->configObject[strtoupper(ConnEnum::YBRCLOUD()->getValue())]) ? $this->configObject[strtoupper(ConnEnum::YBRCLOUD()->getValue())] : null;
            if ($initObj){
                $this->failOver = $initObj->getFailOverEnable();
            }
            $this->connType = strtoupper($connObj->getValue());
        }
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
     * @param string $connType
     * @return object
     * @throws ConnException
     */
    public function initConfigure($conf, $connType)
    {
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
            $f->setConnType($conf['connType']);
            if (!empty($conf['connEnumClass']) && class_exists($conf['connEnumClass'])){
                $f->setConnEnumClass($conf['connEnumClass']);
            }else{
                $f->setConnEnumClass("\Ybren\Codis\Enum\ConnEnum");
            }

            $f->setZkHost($conf['zkHost']);
            $f->setZkPassword($conf['zkPassword']);
            !$conf['retryTime'] ? $f->setRetryTime(3) : $f->setRetryTime($conf['retryTime']);
            $f->setZkName($conf['zkName']);
            $f->setFailOverEnable($conf['failOverEnable']);
            return $f;
        }else{
            $f = new RedisConf();
            $f->setHost($conf['host']);
            $f->setPassword($conf['password']);
            $f->setPrefix($conf['prefix']);
            !$conf['select'] ? $f->setSelect(0) : $f->setSelect($conf['select']);
            !$conf['expire'] ? $f->setExpire(86400) : $f->setExpire($conf['expire']);
            !$conf['timeout'] ? $f->setTimeout(3) : $f->setTimeout($conf['timeout']);
            $f->setFailOverFlag($conf['failOverFlag']);
            return $f;
        }
    }

    /**
     * 通过分配的连接方式获取句柄
     * @return mixed
     * @throws ConnException
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
            //当连接故障 且超过1个数据源就进行切换
            if (((int) $this->failOver == 1) && !$sock && count($this->configObject) > 1){
                $freeConnPool = $this->configObject;
                unset($freeConnPool[$this->getConnType()]);
                foreach ($freeConnPool as $k=>$v){
                    if ($k == strtoupper((string)ConnEnum::YBRCLOUD())){
                        $sock = $this->initCodis($v);
                    }else{
                        if ((int)$v->getFailOverFlag() == 0){
                            continue;
                        }
                        $sock = $this->initRedis($v);
                    }
                    //连接失败就跳转到下一个
                    if (!$sock){
                        unset($freeConnPool[$k]);
                        continue;
                    }
                }
            }
        }else{
            throw new ConnException( "ERR Constant " . $this->getConnType() . " in Enum Class, Config Information is blank.");
        }
        if ($sock){
            return $sock;
        }
        throw new ConnException("ERR Connection is wrong!!");
    }

    /**
     * 初始化codis
     * @param CodisConf $conf
     * @return \Redis
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