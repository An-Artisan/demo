<?php
namespace app\Models;

use Base;
/**
 * 撮合引擎日志模型
 */
class MatchingLogModel extends \DB\SQL\Mapper
{
    public function __construct() {
        parent::__construct(Base::instance()->get('DB'), 'matching_logs');
    }

    // 根据日志ID查找日志记录
    public function findById($logId) {
        $this->load(['log_id = ?', $logId]);
        return $this->query;
    }

    // 根据订单ID查找日志记录
    public function findByOrderId($orderId) {
        return $this->find(['order_id = ?', $orderId]);
    }

    // 创建新日志记录
    public function createLog($orderId, $action, $details) {
        $this->order_id = $orderId;
        $this->action = $action;
        $this->details = json_encode($details);
        $this->save();
        return $this->log_id;
    }
}


