<?php


namespace Ybren\Codis\Connection;


use Ybren\Codis\Enum\ConnEnum;

interface ConnInterface
{

    /**
     * 设置连接类型
     * @param ConnEnum $type
     * @return mixed
     */
    public function setConnType($type);

    /**
     * 获取连接类型
     * @return mixed
     */
    public function getConnType();

    /**
     * 获取连接句柄
     * @param array $conf
     * @param callable $func
     * @return mixed
     */
    public function getSock(array $conf,callable $func);

    /**
     * 初始化配置文件
     * @param array $conf
     * @return mixed
     */
    public function initConfigure(array $conf);
}