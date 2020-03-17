<?php


namespace Ybren\Codis\Zookeeper;

use Ybren\Codis\Exception\LockException;

class ZkDistributedLock
{
    protected $zk;
    protected $myNode;
    protected $isNotified;
    protected $root;

    private $options = array(
        'zkHost'       => '127.0.0.1:2181',//集群地址
        //'zkPassword'   => '', //zookeeper 账号密码
        'zkTimeout'      => 5, //zookeeper 接收超时时间
    );
    public function __construct($conf, $root = "/locks/"){

        if (!empty($conf)){
            $this->options = array_merge($this->options,$conf);
        }else{
            if(!class_exists("\\think\\Config")){
                throw new LockException("So Far. Only Adapter one for Thinkphp when get config file.");
            }
            $this->options = array_merge($this->options,\think\Config::iniGet('codisConnect'));
        }
        if (!class_exists("Zookeeper")){
            throw new LockException("Zookeeper extend is uninstall.");
        }
        $zk = new \Zookeeper($this->options['zkHost']);
        if(!$zk){
            throw new \Exception('connect zookeeper error');
        }

        $this->zk = $zk;
        $this->root = $root;
    }

    // 获取锁
    public function tryGetDistributedLock($key, $value){
        // 创建根节点
        $this->createRootPath($value);
        // 创建临时顺序节点
        $this->createSubPath($this->root .'/'. $key, $value);
        // 获取锁
        return $this->getLock();
    }

    // 释放锁
    public function releaseDistributedLock(){
        if($this->zk->delete($this->myNode)){
            return true;
        }else{
            return false;
        }
    }

    public function createRootPath($value){
        $aclArray = [
            [
                'perms'  => \Zookeeper::PERM_ALL,
                'scheme' => 'world',
                'id'     => 'anyone',
            ]
        ];
        // 判断根节点是否存在
        if(false == $this->zk->exists($this->root)){
            // 创建根节点
            $result = $this->zk->create($this->root, $value, $aclArray);
            if(false == $result){
                throw new \Exception('create '.$this->root.' fail');
            }
        }

        return true;
    }

    public function createSubPath($path, $value){
        // 全部权限
        $aclArray = [
            [
                'perms'  => \Zookeeper::PERM_ALL,
                'scheme' => 'world',
                'id'     => 'anyone',
            ]
        ];
        /**
         * flags :
         * 0 和 null 永久节点，
         * Zookeeper::EPHEMERAL临时，
         * Zookeeper::SEQUENCE顺序，
         * Zookeeper::EPHEMERAL | Zookeeper::SEQUENCE 临时顺序
         */
        $this->myNode = $this->zk->create($path, $value, $aclArray, \Zookeeper::EPHEMERAL | \Zookeeper::SEQUENCE);
        if(false == $this->myNode){
            throw new \Exception('create -s -e '.$path.' fail');
        }
        return true;
    }

    public function getLock(){
        // 获取子节点列表从小到大，显然不可能为空，至少有一个节点
        $res = $this->checkMyNodeOrBefore();
        if($res === true){
            return true;
        }else{
            $this->isNotified = false;// 初始化状态值
            // 考虑监听失败的情况：当我正要监听before之前，它被清除了，监听失败返回 false
            $result = $this->zk->get($res, [\Ybren\Codis\Zookeeper\ZkDistributedLock::class, 'watcher']);
            while(!$result){
                $res1 = $this->checkMyNodeOrBefore();
                if($res1 === true){
                    return true;
                }else{
                    $result = $this->zk->get($res1, [\Ybren\Codis\Zookeeper\ZkDistributedLock::class, 'watcher']);
                }
            }

            // 阻塞，等待watcher被执行，watcher执行完回到这里
            while(!$this->isNotified){
                usleep(100000); // 100ms
            }

            return true;
        }
    }

    /**
     * 通知回调处理
     * @param $type 变化类型 Zookeeper::CREATED_EVENT, Zookeeper::DELETED_EVENT, Zookeeper::CHANGED_EVENT
     * @param $state
     * @param $key 监听的path
     */
    public function watcher($type, $state, $key){
        $this->isNotified = true;
        $this->getLock();
    }

    public function checkMyNodeOrBefore(){
        $list = $this->zk->getChildren($this->root);
        sort($list);
        $root = $this->root;
        array_walk($list, function(&$val) use ($root){
            $val = $root . '/' . $val;
        });

        if($list[0] == $this->myNode){
            return true;
        }else{
            // 找到上一个节点
            $index = array_search($this->myNode, $list);
            $before = $list[$index - 1];
            return $before;
        }
    }
}