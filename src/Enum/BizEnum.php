<?php


namespace Ybren\Codis\Enum;

/**
 * 业务枚举类前缀
 * Class BizEnum
 * @package Ybren\Codis\Enum
 */
class BizEnum extends Enum
{
    /**
     * 默认前缀
     */
    const NORMAL = "normal_";

    /**
     * 订单
     */
    const ORDER  = "order_";

    /**
     * 支付
     */
    const PAY    = "pay_";

    /**
     * 面料
     */
    const FABRIC  = "fabric_";

    /**
     * 工艺
     */
    const CRAFT = "craft_";
}