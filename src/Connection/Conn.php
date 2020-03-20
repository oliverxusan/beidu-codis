<?php


namespace Ybren\Codis\Connection;


use Ybren\Codis\Config\Conf;
use Ybren\Codis\Enum\ConnEnum;
use Ybren\Codis\Exception\ConnException;

/**
 * 连接类型
 * Class Conn
 * @package Ybren\Codis\Connection
 */
class Conn implements ConnInterface
{

    private $connType = null;
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
     * @param array $conf
     * @param callable $func
     * @return mixed
     * @throws ConnException
     */
    public function getSock(array $conf, callable $func)
    {
        $confObj = $this->initConfigure($conf);
        if ($this->connType != null) {
            $confObj->setConnType($this->connType);
        }
        return $func($confObj->getConnType(), $confObj);
    }

    /**
     * 初始化配置文件
     * @param array $conf
     * @return mixed
     * @throws ConnException
     */
    public function initConfigure(array $conf)
    {
        if (empty($conf)){
            throw new ConnException("config set is nil");
        }
        $f = new Conf();
        $f->setConnType($conf['connType']);
        $f->setZkPassword($conf['zkPassword']);
        return $f;
    }
}