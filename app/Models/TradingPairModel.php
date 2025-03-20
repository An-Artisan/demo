<?php
namespace App\Models;

/**
 * 交易对模型
 */
class TradingPairModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(Base::instance()->get('DB'), 'trading_pairs');
    }

    // 根据交易对ID查找交易对
    public function findById($pairId) {
        $this->load(['pair_id = ?', $pairId]);
        return $this->query;
    }

    // 查找所有可用交易对
    public function findAllActive() {
        return $this->find(['status = ?', 0]);
    }
}

