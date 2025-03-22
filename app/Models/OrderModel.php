<?php
namespace app\Models;

use Base;
/**
 * 订单模型
 */
class OrderModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(Base::instance()->get('DB'), 'orders');
    }

    // 根据订单ID查找订单
    public function findById($orderId) {
        $this->load(['order_id = ?', $orderId]);
        return $this->query;
    }

    // 根据用户ID查找用户订单
    public function findByUserId($userId) {
        return $this->find(['user_id = ?', $userId]);
    }

    // 创建新订单
    public function createOrder($userId, $pairId, $type, $side, $price, $amount) {
        $this->user_id = $userId;
        $this->pair_id = $pairId;
        $this->type = $type;
        $this->side = $side;
        $this->price = $price;
        $this->amount = $amount;
        $this->save();
        return $this->order_id;
    }

    // 更新订单状态
    public function updateStatus($orderId, $status) {
        $this->load(['order_id = ?', $orderId]);
        $this->status = $status;
        $this->save();
    }

    // 查询用户当前委托（带分页）
    public function findCurrentOrders($userId, $limit, $offset) {
        return $this->find([
            'user_id = ? AND status IN (?, ?) LIMIT ? OFFSET ?',
            $userId,
            TradeConstants::STATUS_PENDING,
            TradeConstants::STATUS_PARTIAL,
            $limit,
            $offset
        ]);
    }

    // 查询用户当前委托的总记录数
    public function countCurrentOrders($userId) {
        return $this->count([
            'user_id = ? AND status IN (?, ?)',
            $userId,
            TradeConstants::STATUS_PENDING,
            TradeConstants::STATUS_PARTIAL
        ]);
    }

    // 查询用户历史委托（带分页和排序）
    public function findHistoryOrders($userId, $limit, $offset, $sortField, $sortOrder) {
        return $this->find([
            'user_id = ? ORDER BY ? ? LIMIT ? OFFSET ?',
            $userId,
            $sortField,
            $sortOrder,
            $limit,
            $offset
        ]);
    }

    // 查询用户历史委托的总记录数
    public function countHistoryOrders($userId) {
        return $this->count([
            'user_id = ?',
            $userId
        ]);
    }

    // 查询用户成交记录（带分页和排序）
    public function findFilledOrders($userId, $limit, $offset, $sortField, $sortOrder) {
        return $this->find([
            'user_id = ? AND status = ? ORDER BY ? ? LIMIT ? OFFSET ?',
            $userId,
            TradeConstants::STATUS_FILLED,
            $sortField,
            $sortOrder,
            $limit,
            $offset
        ]);
    }

    // 查询用户成交记录的总记录数
    public function countFilledOrders($userId) {
        return $this->count([
            'user_id = ? AND status = ?',
            $userId,
            TradeConstants::STATUS_FILLED
        ]);
    }
}
