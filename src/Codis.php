<?php
namespace Ybren\Codis;


use Ybren\Codis\Exception\CodisException;
use Ybren\Codis\Zookeeper\RedisFromZk;

/**
 * 分布式缓存 当NOSQL使用
 * Class Codis
 * @package Ybren\Codis
 */
class Codis implements CmdInterface
{
    protected static $options = array(
        'zkHost'       => '127.0.0.1:2181',//集群地址
        'zkPassword'   => '', //zookeeper 账号密码
        'zkName'       => '', //zookeeper项目名称
        'retryTime'    => 3, // zookeeper 重试次数
        'password'     => '',
        'select'       => 0,
        'timeout'      => 3,
        'expire'       => 3600,
        'prefix'       => ''
    );
    protected static $handler;  // 当前操作句柄

    protected static $prefix = '';

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @return void
     * @throws \Exception
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('redis')) {
            throw new CodisException('not support: redis');
        }

        if (!empty($options)){
            static::$options = array_merge(static::$options, $options);
        }else{
            if(!class_exists("\\think\\Config")){
                throw new CodisException("So Far. Only Adapter one for Thinkphp when get config file.");
            }
            static::$options = array_merge(static::$options,\think\Config::iniGet('codisConnect'));
        }

        static::$handler = RedisFromZk::connection(static::$options);

        if (static::$options['prefix']) {
            static::$prefix = $options['prefix'];
        }

        if ('' != $options['password']) {
            static::$handler->auth($options['password']);
        }
        if (isset($options['select']) && 0 != $options['select']) {
            static::$handler->select($options['select']);
        }
    }

    private static function connect($options = array()){
        if (!empty(static::$handler)){
            return static::$handler;
        }
        if (!extension_loaded('redis')) {
            throw new CodisException('not support: redis');
        }
        if(!empty($options)){
            static::$options = array_merge(static::$options, $options);
        }else{
            if(!class_exists("\\think\\Config")){
                throw new CodisException("So Far. Only Adapter one for Thinkphp when get config file.");
            }
            static::$options = array_merge(static::$options,\think\Config::iniGet('codisConnect'));
        }

        static::$handler = RedisFromZk::connection(static::$options);

        if (static::$options['prefix']) {
            static::$prefix = $options['prefix'];
        }

        if ('' != $options['password']) {
            static::$handler->auth($options['password']);
        }
        if (isset($options['select']) && 0 != $options['select']) {
            static::$handler->select($options['select']);
        }
        return static::$handler;
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return static::$handler->get($this->getCacheKey($name)) ? true : false;
    }

    /**
     * 获取原始数据
     * @param $name
     * @return mixed
     */
    public function getUnChange($name){
        return static::$handler->get($this->getCacheKey($name));
    }

    /**
     * 保存原始数据
     * @param $name
     * @param $value
     * @param $expire
     * @return mixed
     */
    public function setUnChange($name , $value , $expire = null){
        if (is_null($expire)) {
            $expire = static::$options['expire'];
        }
        $key = $this->getCacheKey($name);
        if (is_int($expire) && $expire) {
            $result = static::$handler->setex($key, $expire, $value);
        } else {
            $result = static::$handler->set($key, $value);
        }
        return $result;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $value = static::$handler->get($this->getCacheKey($name));
        if (is_null($value) || false === $value) {
            return $default;
        }
        $jsonData = json_decode($value, true);
        // 检测是否为JSON数据 true 返回JSON解析数组, false返回源数据 byron sampson<xiaobo.sun@qq.com>
        return (null === $jsonData) ? $value : $jsonData;
    }

    /**
     * 写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param integer   $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire) || empty($expire)) {
            $expire = static::$options['expire'];
        }
        $key = $this->getCacheKey($name);
        //对数组/对象数据进行缓存处理，保证数据完整性  byron sampson<xiaobo.sun@qq.com>
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = static::$handler->setex($key, $expire, $value);
        } else {
            $result = static::$handler->set($key, $value);
        }
        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return static::$handler->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return static::$handler->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return static::$handler->delete($this->getCacheKey($name));
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return object
     */
    public function handler()
    {
        return static::$handler;
    }

    /**
     * 获取实际的缓存标识
     * @access public
     * @param string $name 缓存名
     * @return string
     */
    public function getCacheKey($name)
    {
        return static::$prefix . $name;
    }

    public function lPop($key){
        return static::$handler->lPop($this->getCacheKey($key));
    }

    public function incr($key){
        return static::$handler->incr($this->getCacheKey($key));
    }

    public function decr($key){
        return static::$handler->decr($this->getCacheKey($key));
    }

    public function rPush($key , $val){
        return static::$handler->rPush($this->getCacheKey($key) , $val);
    }

    public function hGet($key , $k1){
        return static::$handler->hGet($this->getCacheKey($key) , $k1);
    }

    public function hSet($key , $k1 , $v1){
        return static::$handler->hSet($this->getCacheKey($key) , $k1 , $v1);
    }

    public function hGetAll($key){

        return static::$handler->hGetAll($this->getCacheKey($key));
    }

    public function hDel($key , $k1){
        return static::$handler->hDel($this->getCacheKey($key) , $k1);
    }

    public function setNx($key , $expire = 300 , $value = 1){
        $cacheKey = $this->getCacheKey($key);
        $result = static::$handler->setNx($cacheKey , $value);
        if($result){
            static::$handler->expire($cacheKey , $expire);
        }
        return $result;
    }

    public function expire($key , $expire = 3600){
        $cacheKey = $this->getCacheKey($key);
        return static::$handler->expire($cacheKey, $expire);
    }

    // 调用静态方法
    public static function __callStatic($method, $params){
        return call_user_func_array(array(static::connect(), $method), $params);
    }
}