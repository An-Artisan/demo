<?php

namespace app\Services;

use App\Models\OrderModel;
use app\Models\TradingPairModel;
use app\Models\UserModel;


class OrderService
{
    protected $OrderModel;

    public function __construct()
    {
        $this->OrderModel = new OrderModel();
    }

    public function createOrder($data)
    {

        if (empty($data)) {
            logger()->write("Empty data passed to createOrder()", 'error');
            return false;
        }
        // 创建订单
        $orderId = $this->OrderModel->createOrder($order);
        // 返回订单ID
        return $orderId;
    }

    /**
     * 获取某币种（交易对）下最近成交的N条记录
     */
    public function getRecentTradesByPair($pairId, $limit = 10)
    {
        return $this->OrderModel->findRecentByPair($pairId, $limit);
    }

}
