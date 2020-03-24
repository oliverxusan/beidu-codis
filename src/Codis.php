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

    private static $_connType = null;

    /**
     * 不允许new 实例出来
     * Codis constructor.
     */
    private function __construct()
    {
    }

    private static function init(){
        if (!empty(static::$_instance)){
            return static::$_instance;
        }
        static::$_instance = new Cmd(static::$_connType);
        return static::$_instance;
    }


    // 调用静态方法
    public static function __callStatic($method, $params){

        return call_user_func_array(array(static::init(), $method), $params);
    }

    /**
     * 获取当前cmd对象实例
     * @param CmdInterface $cmd
     * @return object
     */
    public static function getInstance(CmdInterface $cmd)
    {
        if (static::$_instance == null){
            static::init();
        }
        return static::$_instance;
    }

    public static function switchConnType($type){
        if (!empty($type)){
            static::$_connType = $type;
        }
    }
}