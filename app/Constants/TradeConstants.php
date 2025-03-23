<?php
namespace app\Constants;

/**
 * 交易常量
 */
class TradeConstants {
    // 交易类型
    const TYPE_LIMIT = 0; // 限价单
    const TYPE_MARKET = 1; // 市价单

    // 交易方向
    const SIDE_BUY = 0; // 买入
    const SIDE_SELL = 1; // 卖出

    // 订单状态
    const STATUS_PENDING = 0; // 待成交
    const STATUS_PARTIAL = 1; // 部分成交
    const STATUS_FILLED = 2; // 完全成交
    const STATUS_CANCELED = 3; // 已取消


    //buffer
    const MARKET_ORDER_BUFFER_RATE = 0.01;

    // 固定汇率
    const RATE_BTC = 80000;
    const RATE_ETH = 2000;
}

