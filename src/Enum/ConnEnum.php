<?php


namespace Ybren\Codis\Enum;

/**
 * 连接类型枚举
 * Class ConnEnum
 * @package Ybren\Codis\Enum
 */
class ConnEnum
{
    /**
     * 阿里云缓存
     */
    const ALICLOUD = "ALICLOUD";

    /**
     * 衣邦人缓存
     */
    const YBRCLOUD = "CODIS";

    /**
     * 本地缓存
     */
    const LOCAL    = "LOCAL";
}