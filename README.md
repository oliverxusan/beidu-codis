# beidu-codis
>呗嘟分布式redis扩展

####composer 依赖安装
````
composer require beidu/codis
````

*配置文件*
````
codisConnect.zkHost = '127.0.0.1:2181'
codisConnect.zkPassword = 'username:password'
codisConnect.zkName = 'codis项目名称'
codisConnect.zkTimeout = 5
codisConnect.retryTime = 3
codisConnect.password = 'redis密码'
codisConnect.select = 0 
codisConnect.timeout = 3
codisConnect.expire = 3600
codisConnect.prefix = ''
codisConnect.failOverEnable = 1 //故障转移 1开启 0禁用
codisConnect.connType = 'CODIS' //根据连接枚举类来获取不同数据源 名称 不设置默认是CODIS 
codisConnect.connEnumClass = '\Ybren\Codis\SockEnum' //设置连接枚举类 如果设置了就可以设置不同的connType 值 不设置默认是\Ybren\Codis\Enum\ConnEnum

可以N个连接配置 根据你继承ConnEnum 枚举类
默认3种数据配置源
alicloudConnect.host = '127.0.0.1:6379'
alicloudConnect.password = 'redis密码'
alicloudConnect.select = 0 
alicloudConnect.timeout = 3
alicloudConnect.expire = 3600
alicloudConnect.prefix = ''
alicloudConnect.failOverFlag = 0 //故障转移标志 0禁用 1开启

localConnect.host = '127.0.0.1:6379'
localConnect.password = 'redis密码'
localConnect.select = 0 
localConnect.timeout = 3
localConnect.expire = 3600
localConnect.prefix = ''
localConnect.failOverFlag = 1 //故障转移标志 0禁用 1开启

比如我想增加 SockEnum枚举类
class SockEnum extends ConnEnum{
    protected const SOURCE_ONE = "ONE"
}

那么你要在yaconf 配置中如下配置
oneConnect.host = '127.0.0.1:6379'
oneConnect.password = 'redis密码'
oneConnect.select = 0 
oneConnect.timeout = 3
oneConnect.expire = 3600
oneConnect.prefix = ''

````

>静态调用操作命令


````
string 操作命令
Codis::set($key, $value, $ttl)
Codis::get($key)
Codis::getUnChange($key)
Codis::setUnChange($key, $value, $expire)
Codis::dec($key, $step = 1)
Codis::inc($key, $step = 1)
Codis::rm($key)
Codis::setNx($key, $expire= 300, $value= 1)
Codis::has($name)

获取redis连接句柄可以操作没有的方法
Codis::handler()

list 操作命令
Codis::lPop($key)
Codis::rPush($key, $val)

hash 操作命令
Codis::hGet($key, $k1)
Codis::hSet($key, $k1, $v1)
Codis::hGetAll($key)
Codis::hDel($key, $k1)

设置前缀
Codis::setPrefix("ddd_");
````
> 分布式锁 zookeeper锁和 redis锁

````
$rdLock = new rdLock();

$lock = $rdLock->acquireLock("haha",1);

if ($lock) {
   echo "redis locked ~~~~`\r\n";
}
$rdLock->releaseLock("haha",1);
echo "redis release lock\r\n";

$zkLock = new zkLock();
$res = $zkLock->acquireLock("cd324",444);
if ($res){
    echo "zk locked ~~~~`\r\n";
}
echo "zk start  release lock\r\n";
$zkLock->releaseLock();
echo "zk end release lock\r\n";

````

### 切换连接类型
````
三种类型 Codis分布式缓存 ConnEnum::YBRCLOUD()  阿里云redis ConnEnum::ALICLOUD()  本地redis ConnEnum::LOCAL()

//切换到本地
Codis::switchConnType(ConnEnum::LOCAL());
//获取redis句柄 可以操作redis扩展的命令 除了部分命令不能使用之外其余都可以
$redis = Codis::handler();

//切换封装的命令操作类CMD类  Coids::getInstance()
可以使用 Cmd.php 所有方法 通过静态访问
Coids::set($key,$value,$ttl) 

//增加前缀枚举类 如需自定义 可以继承此枚举类
//枚举订单前缀
BizEnum::ORDER()
...

class Custom extends BizEnum{
    const XX = "HAHAH_"; 自定义前缀
}
````

> Codis禁用命令如下

|   Command Type   |   Command Name   |
|:----------------:|:---------------- |
|   Keys           | KEYS             |
|                  | MIGRATE          |
|                  | MOVE             |
|                  | OBJECT           |
|                  | RANDOMKEY        |
|                  | RENAME           |
|                  | RENAMENX         |
|                  | SCAN             |
|                  |                  |
|   Strings        | BITOP            |
|                  | MSETNX           |
|                  |                  |
|   Lists          | BLPOP            |
|                  | BRPOP            |
|                  | BRPOPLPUSH       |
|                  |                  |
|   Pub/Sub        | PSUBSCRIBE       |
|                  | PUBLISH          |
|                  | PUNSUBSCRIBE     |
|                  | SUBSCRIBE        |
|                  | UNSUBSCRIBE      |
|                  |                  |
|   Transactions   | DISCARD          |
|                  | EXEC             |
|                  | MULTI            |
|                  | UNWATCH          |
|                  | WATCH            |
|                  |                  |
|   Scripting      | SCRIPT           |
|                  |                  |
|   Server         | BGREWRITEAOF     |
|                  | BGSAVE           |
|                  | CLIENT           |
|                  | CONFIG           |
|                  | DBSIZE           |
|                  | DEBUG            |
|                  | FLUSHALL         |
|                  | FLUSHDB          |
|                  | LASTSAVE         |
|                  | LATENCY          |
|                  | MONITOR          |
|                  | PSYNC            |
|                  | REPLCONF         |
|                  | RESTORE          |
|                  | SAVE             |
|                  | SHUTDOWN         |
|                  | SLAVEOF          |
|                  | SLOWLOG          |
|                  | SYNC             |
|                  | TIME             |
|                  |                  |
|   Codis Slot     | SLOTSCHECK       |
|                  | SLOTSDEL         |
|                  | SLOTSINFO        |
|                  | SLOTSMGRTONE     |
|                  | SLOTSMGRTSLOT    |
|                  | SLOTSMGRTTAGONE  |
|                  | SLOTSMGRTTAGSLOT |


> 半支持命令

|   Command Type   |   Command Name   |
|:----------------:|:---------------- |
|   Lists          | RPOPLPUSH        |
|                  |                  |
|   Sets           | SDIFF            |
|                  | SINTER           |
|                  | SINTERSTORE      |
|                  | SMOVE            |
|                  | SUNION           |
|                  | SUNIONSTORE      |
|                  |                  |
|   Sorted Sets    | ZINTERSTORE      |
|                  | ZUNIONSTORE      |
|                  |                  |
|   HyperLogLog    | PFMERGE          |
|                  |                  |
|   Scripting      | EVAL             |
|                  | EVALSHA          |
