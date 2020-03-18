# beidu-codis
>呗嘟分布式redis扩展

*配置文件*
````
codisConnect.zkHost = '172.16.24.2:2181,172.16.24.1:2181,172.16.24.3:2181'
codisConnect.zkPassword = 'oliver:123456as'
codisConnect.zkName = 'ybren-cache-middleware'
codisConnect.zkTimeout = 5
codisConnect.retryTime = 3
codisConnect.password = '123456as'
codisConnect.select = 0
codisConnect.timeout = 3
codisConnect.expire = 3600
codisConnect.prefix = ''
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

获取redis连接句柄可以操作没有的对象
Codis::handler()

list 操作命令
Codis::lPop($key)
Codis::rPush($key, $val)

hash 操作命令
Codis::hGet($key, $k1)
Codis::hSet($key, $k1, $v1)
Codis::hGetAll($key)
Codis::hDel($key, $k1)
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