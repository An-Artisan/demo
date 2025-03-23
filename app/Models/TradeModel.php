<?php
namespace app\Models;

use Base;
class TradeModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(db(), 'trades');
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
        $model = new $this;
        $model->taker_order_id = $takerOrderId;
        $model->maker_order_id = $makerOrderId;
        $model->pair_id = $pairId;
        $model->price = $price;
        $model->amount = $amount;
        $model->fee = $fee;
        $model->save();
        return $this->trade_id;
    }
}

