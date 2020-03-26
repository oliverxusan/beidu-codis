<?php
namespace Ybren\Codis;


use Ybren\Codis\Enum\ConnEnum;
use Ybren\Codis\Exception\ConnException;

/**
 * 分布式缓存 当NOSQL使用
 * Class Codis
 * @method static Codis getUnChange($name)
 * @method static Codis setUnChange($name , $value , $expire = null)
 * @method static Codis get($name, $default = false)
 * @method static Codis set($name, $value, $expire = null)
 * @method static Codis inc($name, $step = 1)
 * @method static Codis dec($name, $step = 1)
 * @method static Codis rm($name)
 * @method static Codis handler()
 * @method static Codis getCacheKey($name)
 * @method static Codis lPop($key)
 * @method static Codis incr($key)
 * @method static Codis decr($key)
 * @method static Codis rPush($key , $val)
 * @method static Codis hGet($key , $k1)
 * @method static Codis hSet($key , $k1 , $v1)
 * @method static Codis hGetAll($key)
 * @method static Codis hDel($key , $k1)
 * @method static Codis setNx($key , $expire = 300 , $value = 1)
 * @method static Codis has($name)
 * @method static Codis expire($key , $expire = 3600)
 * @method static Codis setPrefix($value)
 * @method static Codis getPrefix()
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
        if (empty(static::$_connType)){
            static::$_connType = ConnEnum::YBRCLOUD();
        }
        if (isset(static::$_instance[(string)static::$_connType])){
            return static::$_instance[(string)static::$_connType];
        }
        return static::$_instance[(string)static::$_connType] = new Cmd(static::$_connType);
    }


    // 调用静态方法
    public static function __callStatic($method, $params){
        return call_user_func_array(array(static::init(), $method), $params);
    }

    /**
     * 获取当前cmd对象实例
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(static::$_instance[(string)static::$_connType])){
            return static::init();
        }
        return static::$_instance[(string)static::$_connType];
    }

    /**
     * 切换数据源
     * @param $enumObj 枚举对象值
     * @throws ConnException
     */
    public static function switchConnType($enumObj){
        if (!$enumObj instanceof ConnEnum){
            throw new ConnException("It must is a instance which is ConnEnum class. Or given one which is extends of ConnEnum class instance ");
        }
        static::$_connType = $enumObj;
    }
}