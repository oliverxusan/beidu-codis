<?php


namespace Ybren\Codis\Enum;

/**
 * 连接类型枚举
 * Class ConnEnum
 * @method static ConnEnum ALICLOUD()
 * @method static ConnEnum YBRCLOUD()
 * @method static ConnEnum LOCAL()
 * @package Ybren\Codis\Enum
 */
class ConnEnum extends Enum
{
    /**
     * 阿里云缓存
     */
    protected const ALICLOUD = "ALICLOUD";

    /**
     * 衣邦人缓存
     */
    protected const YBRCLOUD = "CODIS";

    /**
     * 本地缓存
     */
    protected const LOCAL    = "LOCAL";

}