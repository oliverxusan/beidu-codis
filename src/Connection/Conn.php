<?php


namespace Ybren\Codis\Connection;


use Ybren\Codis\Config\Conf;
use Ybren\Codis\Enum\ConnEnum;

/**
 * 连接类型
 * Class Conn
 * @package Ybren\Codis\Connection
 */
class Conn implements ConnInterface
{

    private $connType = ConnEnum::YBRCLOUD;
    /**
     * 设置连接类型
     * @param ConnEnum $type
     * @return mixed
     */
    public function setConnType($type)
    {
        $this->connType = $type;
    }

    /**
     * 获取连接类型
     * @return mixed
     */
    public function getConnType()
    {
        return $this->connType;
    }

    /**
     * 获取连接句柄
     * @return mixed
     */
    public function getSock()
    {
        $conf = new Conf();

        switch (strtoupper($this->connType)){
            case ConnEnum::YBRCLOUD:
                $conf->setZkPassword();
                break;
            case ConnEnum::ALICLOUD;
                break;
            case ConnEnum::LOCAL;
                break;
            default:
                break;
        }

    }
}