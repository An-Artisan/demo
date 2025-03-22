<?php
namespace app\Services;

use App\Constants\TradeConstants;
use App\Models\AssetLedgerModel;
use App\Models\OrderModel;
use App\Models\TradeModel;
use App\Models\TradingPairModel;

class MatchingEngineService {
    // 撮合订单
    /**
     * @throws \Exception
     */
    public function matchOrders($pairId) {
        $db = db();
        $orderModel = new OrderModel();
        $tradeModel = new TradeModel();
        $assetLedgerModel = new AssetLedgerModel();

        try {
            $db->begin();

            $buyOrders = $orderModel->find(['pair_id = ? AND side = ? AND status = ?', $pairId, TradeConstants::SIDE_BUY, TradeConstants::STATUS_PENDING]);
            $sellOrders = $orderModel->find(['pair_id = ? AND side = ? AND status = ?', $pairId, TradeConstants::SIDE_SELL, TradeConstants::STATUS_PENDING]);

            foreach ($buyOrders as $buyOrder) {
                foreach ($sellOrders as $sellOrder) {
                    if (bccomp($buyOrder->price, $sellOrder->price, 8) >= 0) {
                        $buyRemain = bcsub($buyOrder->amount, $buyOrder->filled_amount, 8);
                        $sellRemain = bcsub($sellOrder->amount, $sellOrder->filled_amount, 8);
                        $tradeAmount = bccomp($buyRemain, $sellRemain, 8) <= 0 ? $buyRemain : $sellRemain;

                        $buyOrder->filled_amount = bcadd($buyOrder->filled_amount, $tradeAmount, 8);
                        $sellOrder->filled_amount = bcadd($sellOrder->filled_amount, $tradeAmount, 8);

                        if (bccomp($buyOrder->filled_amount, $buyOrder->amount, 8) >= 0) {
                            $buyOrder->status = TradeConstants::STATUS_FILLED;
                        }
                        if (bccomp($sellOrder->filled_amount, $sellOrder->amount, 8) >= 0) {
                            $sellOrder->status = TradeConstants::STATUS_FILLED;
                        }

                        $buyOrder->save();
                        $sellOrder->save();

                        $tradeModel->createTrade(
                            $buyOrder->order_id,
                            $sellOrder->order_id,
                            $pairId,
                            $sellOrder->price,
                            $tradeAmount,
                            0
                        );

                        $this->updateUserAssets(
                            $buyOrder->user_id,
                            $sellOrder->user_id,
                            $pairId,
                            $tradeAmount,
                            $sellOrder->price
                        );
                    }
                }
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    private function updateUserAssets($buyerId, $sellerId, $pairId, $amount, $price) {
        $assetLedgerModel = new AssetLedgerModel();
        $tradingPairModel = new TradingPairModel();
        $pair = $tradingPairModel->findById($pairId);

        $totalQuote = bcmul($amount, $price, 8);
        $negAmount = bcmul($amount, '-1', 8);
        $negQuote = bcmul($totalQuote, '-1', 8);

        // 买家
        $assetLedgerModel->createLedger($buyerId, $pair->quote_currency, $negQuote, 2);
        $assetLedgerModel->createLedger($buyerId, $pair->base_currency, $amount, 2);

        // 卖家
        $assetLedgerModel->createLedger($sellerId, $pair->quote_currency, $totalQuote, 2);
        $assetLedgerModel->createLedger($sellerId, $pair->base_currency, $negAmount, 2);
    }
}
