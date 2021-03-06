<?php
namespace Ybren\Codis;


use Ybren\Codis\Connection\Conn;
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

    /**
     * 连接对象
     * @var null
     */
    private static $_connObj = null;

    /**
     * 连接类型
     * @var null
     */
    private static $_connType = null;

    /**
     * 不允许new 实例出来
     * Codis constructor.
     */
    private function __construct()
    {
    }

    private static function connect(){

        if (isset(static::$_instance[static::$_connType])){
            return static::$_instance[static::$_connType];
        }
        $conn = new Conn(static::$_connObj);
        static::$_connType = $conn->getConnType();
        return static::$_instance[static::$_connType] = new Cmd($conn);
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
        if (!isset(static::$_instance[static::$_connType])){
            return static::connect();
        }
        return static::$_instance[static::$_connType];
    }

    /**
     * 切换数据源
     * @param ConnEnum $enumObj 枚举对象值
     */
    public static function init(ConnEnum $enumObj){
        static::$_connObj = $enumObj;
    }
}