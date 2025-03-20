<?php
namespace App\Models;

class TradeModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(config('database'), 'trades');
    }

    // 根据交易ID查找交易记录
    public function findById($tradeId) {
        $this->load(['trade_id = ?', $tradeId]);
        return $this->query;
    }

    // 根据交易对ID查找交易记录
    public function findByPairId($pairId) {
        return $this->find(['pair_id = ?', $pairId]);
    }

    // 创建新交易记录
    public function createTrade($takerOrderId, $makerOrderId, $pairId, $price, $amount, $fee) {
        $this->taker_order_id = $takerOrderId;
        $this->maker_order_id = $makerOrderId;
        $this->pair_id = $pairId;
        $this->price = $price;
        $this->amount = $amount;
        $this->fee = $fee;
        $this->save();
        return $this->trade_id;
    }
}

