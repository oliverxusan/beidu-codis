<?php


namespace Ybren\Codis\Zookeeper;


use Ybren\Codis\Config\CodisConf;
use Ybren\Codis\Config\Conf;
use Ybren\Codis\Exception\CodisException;

class RedisFromZk
{
    private static $_zkConnector ;
    private static $_proxyName ;
    private static $_proxyNum ;

    private static $_codisInstance ;

    private static $timeout = 3;

    /**
     * @param CodisConf $conf
     * @return Object Redis
     * @throws CodisException
     */
    public static function connection(CodisConf $conf){
        static::$timeout = $conf->getTimeout();
        return static::getCodisInstance($conf->getZkHost(), "/jodis/".$conf->getZkName(),$conf->getZkPassword(), $conf->getRetryTime());
    }

    /**
     * @param [string] $address [e.g. "host1:2181,host2:2181"]
     * @return Object
     * @throws CodisException
     */
    private static function _getZkConnector($address)
    {
        if(self::$_zkConnector !== null ){
            return self::$_zkConnector ;
        }else{
            return self::$_zkConnector = new ZookeeperClient($address) ;
        }
    }

    private static function _setProxyName($proxyName){
        self::$_proxyName = $proxyName ;
    }

    public static function getProxyName(){
        return self::$_proxyName ;
    }

    private static function _setProxyNum($proxyNum){
        self::$_proxyNum = $proxyNum ;
    }

    public static function getProxyNum(){
        return self::$_proxyNum ;
    }

    public static function getCodisInstance($address, $proxyPath, $password = "", $retryTime=1){

        if(!self::$_codisInstance){
            if (!empty($password)) {
                if (!strstr($password,":")){
                    throw new CodisException("The Password with Format is wrong. Fx: username:password");
                }
            }
            $redis = new \Redis() ;

            //until get a avalible proxy node
            $proxyNum = 0 ;
            do{
                $proxy = self::selectProxy($address, $password, $proxyPath) ;
                $proxyNum++ ;

                $addr = explode(':', $proxy) ;
                $connector = $redis->connect($addr[0], $addr[1], static::$timeout) ;
                if(!$connector){
                    $i = 0 ;
                    //retry
                    while($i < $retryTime){
                        $connector = $redis->connect($addr[0], $addr[1], static::$timeout);
                        if (!empty($redisPassword)){
                            $redis->auth($redisPassword);
                        }
                        $i++ ;
                        if($connector){
                            self::$_codisInstance = $redis ;
                            break ;
                        }
                    }

                    if($i == $retryTime){
                        //delete
                        self::deleteProxy($address, $proxyPath, self::getProxyName()) ;
                    }

                }else{
                    self::$_codisInstance = $redis ;
                    break ;
                }

            }while(!self::$_codisInstance && $proxyNum<=self::getProxyNum()) ;
        }

        return self::$_codisInstance ;

    }
    /**
     * 获得一个codis代理地址
     *
     */
    public static function selectProxy($address,$password, $proxyPath)
    {
        if(substr($proxyPath, -1) == '/'){ //if the last char is "/" then delete it
            $proxyPath = substr($proxyPath, 0, -1) ;
        }
        if (!empty($password)){
            $isAuth = self::_getZkConnector($address)->addAuth("digest",$password,function (){});
            if (!$isAuth){
                throw new CodisException("Zookeeper Auth has fail!");
            }
        }

        $proxyNodes = self::_getZkConnector($address)->getChildren($proxyPath) ;

        if(is_array($proxyNodes)){
            $proxyNum = count($proxyNodes) ;
            $proxyName = $proxyNodes[rand(0, $proxyNum-1)] ;
            $proxyStr = self::_getZkConnector($address)->get($proxyPath.'/'.$proxyName) ;
            if(strlen($proxyStr)>0 && $proxyInfo = json_decode($proxyStr, true)){
                self::_setProxyName($proxyName) ;
                self::_setProxyNum($proxyNum) ;
                return $proxyInfo['addr'] ;
            }
        }
        throw new CodisException("Codis Proxy node is not found!");
    }

    /**
     * 移除zk节点
     */
    public static function deleteProxy($address, $proxyPath, $proxyName)
    {
        if(substr($proxyPath, -1) == '/'){ //if the last char is "/" then delete it
            $proxyPath = substr($proxyPath, 0, -1) ;
        }
        return self::_getZkConnector($address)->removeNode($proxyPath.'/'.$proxyName) ;
    }
}