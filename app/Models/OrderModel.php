<?php
namespace app\Models;

use Base;
use app\Constants\TradeConstants;
/**
 * 订单模型
 */
class OrderModel extends \DB\SQL\Mapper {
    protected $table = 'orders';

    public function __construct() {
        parent::__construct(db(), $this->table);
    }

    // 根据订单ID查找订单
    public function findById($orderId) {
        return $this->load(['order_id = ?', $orderId]);
    }

    // 根据用户ID查找用户订单
    public function findByUserId($userId) {
        return $this->find(['user_id = ?', $userId]);
    }

    // 创建新订单
    public function createOrder($order) {
        $this->order_id = intval(time().rand(100,999).rand(100,999));
        $this->user_id = $order['user_id'];
        $this->pair_id = $order['pair_id'];
        $this->type = $order['type'];
        $this->side = $order['side'];
        $this->price = $order['price'];
        $this->amount = $order['amount'];
        $this->lock_amount = $order['lock_amount'];
        $this->status = TradeConstants::STATUS_PENDING;
        $this->filled_amount = 0;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
        return $this->order_id;
    }

    // 取消订单
    public function cancelOrder($orderId) {
        $this->load(['order_id = ?', $orderId]);
        $this->status = TradeConstants::STATUS_CANCELED;
        return $this->save();
    }


    // 更新订单状态
    public function updateStatus($orderId, $status) {
        $this->load(['order_id = ?', $orderId]);
        $this->status = $status;
        return $this->save();
    }

    // 查询用户当前委托
    public function findCurrentOrders($userId, $pairId) {
        return $this->find([
            'user_id = ? AND status IN (?, ?) AND pair_id = ? ORDER BY created_at DESC',
            $userId,
            TradeConstants::STATUS_PENDING,
            TradeConstants::STATUS_PARTIAL,
            $pairId,
        ]);

    }

    public function findCurrentOrdersAll($pairId,$limit) {
        return $this->find([
            ' status IN (?, ?) and pair_id = ? and type = ?  ORDER BY `created_at` desc LIMIT ? ',
            TradeConstants::STATUS_PENDING,
            TradeConstants::STATUS_PARTIAL,
            $pairId,
            TradeConstants::TYPE_LIMIT,
            $limit,
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

    // 查询用户历史委托
    public function findHistoryOrders($userId, $pairId) {
        return $this->find([
            'user_id = ? AND pair_id = ? ORDER BY created_at desc',
            $userId,
            $pairId,
        ]);
    }

    // 查询用户历史委托的总记录数
    public function countHistoryOrders($userId) {
        return $this->count([
            'user_id = ?',
            $userId
        ]);
    }

    // 查询用户成交记录（从 trades 联表 order 获取完整订单信息）
    public function findFilledOrders($userId, $pairId, $sortField = 'created_at', $sortOrder = 'DESC') {

        $sql = "
        SELECT 
            o.order_id, 
            o.pair_id, 
            o.type, 
            o.side, 
            t.price, 
            t.amount, 
            o.filled_amount, 
            o.status, 
            t.created_at
        FROM trades t
        JOIN orders o 
            ON t.taker_order_id = o.order_id OR t.maker_order_id = o.order_id
        WHERE o.user_id = ? AND o.pair_id = ?
        ORDER BY t." . $sortField . " " . $sortOrder;

        $result = $this->db->exec($sql, [$userId, $pairId]);

        return $result;
    }
    // 查询用户成交记录的总记录数
    public function countFilledOrders($userId) {
        return $this->count([
            'user_id = ? AND status = ?',
            $userId,
            TradeConstants::STATUS_FILLED
        ]);
    }


    public function findRecentByPair($pairId, $limit = 10)
    {
        $sql = "
        SELECT 
            t.trade_id,
            t.price,
            t.amount,
            t.fee,
            t.created_at,
            o.side,
            o.type
        FROM trades t
        LEFT JOIN orders o ON o.order_id = t.taker_order_id
        WHERE t.pair_id = ?
        ORDER BY t.created_at DESC
        LIMIT ?
    ";
        return $this->db->exec($sql, [$pairId, $limit]);
    }


    /**
     * 获取活跃的买单：状态为未成交或部分成交，按市价优先，限价价格从高到低
     */
    public function getOpenBuyOrders(string $pairId): array {
        return $this->find(
            [
            "pair_id = ? AND side = ? AND status IN (?, ?) ORDER BY type ASC, price DESC, created_at ASC",
                $pairId,
                TradeConstants::SIDE_BUY,
                TradeConstants::STATUS_PENDING,
                TradeConstants::STATUS_PARTIAL
            ]
        );
    }

    /**
     * 获取活跃的卖单：状态为未成交或部分成交，按市价优先，限价价格从低到高
     */
    public function getOpenSellOrders(string $pairId): array {
        return $this->find(
            [
                "pair_id = ? AND side = ? AND status IN (?, ?) ORDER BY type ASC, price ASC, created_at ASC",
                $pairId,
                TradeConstants::SIDE_SELL,
                TradeConstants::STATUS_PENDING,
                TradeConstants::STATUS_PARTIAL
            ]
        );
    }
}

