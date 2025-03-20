<?php
namespace App\Models;

use App\Constants\TradeConstants;
class MatchingEngineModel {
    // 撮合订单
    public function matchOrders($pairId) {
        $db = config("database");
        $orderModel = new OrderModel();
        $tradeModel = new TradeModel();
        $assetLedgerModel = new AssetLedgerModel();

        try {
            // 开启事务
            $db->begin();

            // 获取未成交的买单和卖单
            $buyOrders = $orderModel->find(['pair_id = ? AND side = ? AND status = ?', $pairId, TradeConstants::SIDE_BUY, TradeConstants::STATUS_PENDING]);
            $sellOrders = $orderModel->find(['pair_id = ? AND side = ? AND status = ?', $pairId, TradeConstants::SIDE_SELL, TradeConstants::STATUS_PENDING]);

            // 撮合逻辑
            foreach ($buyOrders as $buyOrder) {
                foreach ($sellOrders as $sellOrder) {
                    // 检查价格是否匹配（限价单）
                    if ($buyOrder->price >= $sellOrder->price) {
                        // 计算成交数量
                        $tradeAmount = min($buyOrder->amount - $buyOrder->filled_amount, $sellOrder->amount - $sellOrder->filled_amount);

                        // 更新订单状态
                        $buyOrder->filled_amount += $tradeAmount;
                        $sellOrder->filled_amount += $tradeAmount;
                        if ($buyOrder->filled_amount >= $buyOrder->amount) {
                            $buyOrder->status = TradeConstants::STATUS_FILLED; // 完全成交
                        }
                        if ($sellOrder->filled_amount >= $sellOrder->amount) {
                            $sellOrder->status = TradeConstants::STATUS_FILLED; // 完全成交
                        }
                        $buyOrder->save();
                        $sellOrder->save();

                        // 创建交易记录
                        $tradeModel->createTrade($buyOrder->order_id, $sellOrder->order_id, $pairId, $sellOrder->price, $tradeAmount, 0);

                        // 更新用户资产
                        $this->updateUserAssets($buyOrder->user_id, $sellOrder->user_id, $pairId, $tradeAmount, $sellOrder->price);
                    }
                }
            }

            // 提交事务
            $db->commit();
        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            throw $e; // 抛出异常以便上层处理
        }
    }

    // 更新用户资产
    private function updateUserAssets($buyerId, $sellerId, $pairId, $amount, $price) {
        $assetLedgerModel = new AssetLedgerModel();
        $tradingPairModel = new TradingPairModel();
        $pair = $tradingPairModel->findById($pairId);

        // 买家减少计价货币，增加基础货币
        $assetLedgerModel->createLedger($buyerId, $pair->quote_currency, -($amount * $price), 2);
        $assetLedgerModel->createLedger($buyerId, $pair->base_currency, $amount, 2);

        // 卖家增加计价货币，减少基础货币
        $assetLedgerModel->createLedger($sellerId, $pair->quote_currency, $amount * $price, 2);
        $assetLedgerModel->createLedger($sellerId, $pair->base_currency, -$amount, 2);
    }
}
