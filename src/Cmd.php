<?php


namespace Ybren\Codis;


use Ybren\Codis\Exception\CodisException;
use Ybren\Codis\Zookeeper\RedisFromZk;

class Cmd implements CmdInterface
{

    protected $options = array(
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

    protected $handler;  // 当前操作句柄

    protected $prefix = '';
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
            $this->options = array_merge($this->options, $options);
        }else{
            if(!class_exists("\\think\\Config")){
                throw new CodisException("So Far. Only Adapter one for Thinkphp when get config file.");
            }
            $this->options = array_merge($this->options,\think\Config::iniGet('codisConnect'));
        }

        $this->handler = RedisFromZk::connection($this->options);

        if ($this->options['prefix']) {
            $this->prefix = $this->options['prefix'];
        }

        if ($this->options['password']) {
            $this->handler->auth($this->options['password']);
        }
        if ($this->options['select'] && $this->options['select'] > 0) {
            $this->handler->select($this->options['select']);
        }
    }
    /**
     * 获取原始数据
     * @param $name
     * @return mixed
     */
    public function getUnChange($name){
        return $this->handler->get($this->getCacheKey($name));
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
            $expire = $this->options['expire'];
        }
        $key = $this->getCacheKey($name);
        if (is_int($expire) && $expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
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
        $value = $this->handler->get($this->getCacheKey($name));
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
            $expire = $this->options['expire'];
        }
        $key = $this->getCacheKey($name);
        //对数组/对象数据进行缓存处理，保证数据完整性  byron sampson<xiaobo.sun@qq.com>
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
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
        return $this->handler->incrby($key, $step);
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
        return $this->handler->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return $this->handler->delete(static::getCacheKey($name));
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 获取实际的缓存标识
     * @access public
     * @param string $name 缓存名
     * @return string
     */
    public function getCacheKey($name)
    {
        return $this->prefix . $name;
    }

    public function lPop($key){
        return $this->handler->lPop($this->getCacheKey($key));
    }

    public function incr($key){
        return $this->handler->incr($this->getCacheKey($key));
    }

    public function decr($key){
        return $this->handler->decr($this->getCacheKey($key));
    }

    public function rPush($key , $val){
        return $this->handler->rPush($this->getCacheKey($key) , $val);
    }

    public function hGet($key , $k1){
        return $this->handler->hGet($this->getCacheKey($key) , $k1);
    }

    public function hSet($key , $k1 , $v1){
        return $this->handler->hSet($this->getCacheKey($key) , $k1 , $v1);
    }

    public function hGetAll($key){

        return $this->handler->hGetAll($this->getCacheKey($key));
    }

    public function hDel($key , $k1){
        return $this->handler->hDel($this->getCacheKey($key) , $k1);
    }

    public function setNx($key , $expire = 300 , $value = 1){
        $cacheKey = $this->getCacheKey($key);
        $result = $this->handler->setNx($cacheKey , $value);
        if($result){
            $this->handler->expire($cacheKey , $expire);
        }
        return $result;
    }
    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->handler->get($this->getCacheKey($name)) ? true : false;
    }

    public function expire($key , $expire = 3600){
        $cacheKey = $this->getCacheKey($key);
        return $this->handler->expire($cacheKey, $expire);
    }
}