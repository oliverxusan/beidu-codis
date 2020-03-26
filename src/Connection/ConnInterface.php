<?php


namespace Ybren\Codis\Connection;


interface ConnInterface
{

    /**
     * 获取连接类型
     * @return mixed
     */
    public function getConnType();

    /**
     * 通过匿名函数获取连接句柄
     * @param callable $callback
     * @return mixed
     */
    public function getSock($callback);

    /**
     * 通过分配的连接方式获取句柄
     * @param $conf
     * @return mixed
     */
    public function getAssignSock();

    /**
     * 初始化配置文件
     * @param array $conf
     * @return mixed
     */
    public function initConfigure($conf);

    /**
     * 获取config对象
     * @return mixed
     */
    public function getConfObj();
}