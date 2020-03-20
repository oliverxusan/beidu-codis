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
     * @return mixed
     */
    public function getSock();
}