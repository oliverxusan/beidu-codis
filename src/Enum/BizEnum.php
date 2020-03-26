<?php


namespace Ybren\Codis\Enum;

/**
 * 业务枚举类前缀
 * Class BizEnum
 * @method static BizEnum NORMAL()
 * @method static BizEnum ORDER()
 * @method static BizEnum PAY()
 * @method static BizEnum FABRIC()
 * @method static BizEnum CRAFT()
 * @package Ybren\Codis\Enum
 */
class BizEnum extends Enum
{
    /**
     * 默认前缀
     */
    protected const NORMAL = "normal_";

    /**
     * 订单
     */
    protected const ORDER  = "order_";

    /**
     * 支付
     */
    protected const PAY    = "pay_";

    /**
     * 面料
     */
    protected const FABRIC  = "fabric_";

    /**
     * 工艺
     */
    protected const CRAFT = "craft_";
}