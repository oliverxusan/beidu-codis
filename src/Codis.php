<?php
namespace Ybren\Codis;

/**
 * 分布式缓存 当NOSQL使用
 * Class Codis
 * @package Ybren\Codis
 */
class Codis
{
    //cmd实例
    private static $_instance = null;

    private function __construct()
    {
    }

    private static function connect($options = array()){
        if (!empty(static::$_instance)){
            return static::$_instance;
        }
        static::$_instance = new Cmd($options);
        return static::$_instance;
    }


    // 调用静态方法
    public static function __callStatic($method, $params){

        return call_user_func_array(array(static::connect(), $method), $params);
    }

    /**
     * 获取当前cmd对象实例
     * @return object
     */
    public static function getInstance()
    {
        if (static::$_instance == null){
            static::connect();
        }
        return static::$_instance;
    }
}