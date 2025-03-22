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


    public function findCurrentOrders($userId, $limit, $offset) {
        return $this->OrderModel->findCurrentOrders($userId, $limit, $offset);
    }

    public function countCurrentOrders($userId) {
        return $this->OrderModel->countCurrentOrders($userId);
    }

    public function findHistoryOrders($userId, $limit, $offset,$sortField, $sortOrder) {
        return $this->OrderModel->findHistoryOrders($userId, $limit, $offset,$sortField, $sortOrder);
    }

    public function countHistoryOrders($userId) {
        return $this->OrderModel->countHistoryOrders($userId);
    }

    public function findFilledOrders($userId, $limit, $offset,$sortField, $sortOrder) {
        return $this->OrderModel->findFilledOrders($userId, $limit, $offset,$sortField, $sortOrder);
    }

    public function countFilledOrders($userId) {

        return $this->OrderModel->countFilledOrders($userId);
    }

    /**
     * 获取某币种（交易对）下最近成交的N条记录
     */
    public function getRecentTradesByPair($pairId, $limit = 10)
    {
        return $this->OrderModel->findRecentByPair($pairId, $limit);
    }

}

