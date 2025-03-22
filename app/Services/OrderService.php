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

    public function createOrder($order)
    {

        if (empty($order)) {
            logger()->write("Empty data passed to createOrder()", 'error');
            return false;
        }
        // 创建订单
        $orderId = $this->OrderModel->createOrder($order);
        // 返回订单ID
        return $orderId;
    }
}