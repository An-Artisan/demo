<?php
namespace app\Models;

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
}
