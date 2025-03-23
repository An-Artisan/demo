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
            // 查询待撮合订单
            //待撮合买家订单
            $buyOrders = $orderModel->find(['pair_id = ? AND side = ? AND status IN (?,?)', $pairId, TradeConstants::SIDE_BUY, TradeConstants::STATUS_PENDING,TradeConstants::STATUS_PARTIAL]);
            //待撮合卖家订单
            $sellOrders = $orderModel->find(['pair_id = ? AND side = ? AND status IN (?,?)', $pairId, TradeConstants::SIDE_SELL, TradeConstants::STATUS_PENDING,TradeConstants::STATUS_PARTIAL]);

            foreach ($buyOrders as $buyOrder) {
                foreach ($sellOrders as $sellOrder) {

                    if ($buyOrder->user_id == $sellOrder->user_id)  {
                        continue;  // 双方是同一用户的订单，不撮合
                    }

                    if (bccomp($buyOrder->price, $sellOrder->price, 8) >= 0) {
                        $buyRemain = bcsub($buyOrder->amount, $buyOrder->filled_amount, 8); // 买家剩余未成交量
                        $sellRemain = bcsub($sellOrder->amount, $sellOrder->filled_amount, 8); // 买家剩余未成交量
                        $tradeAmount = bccomp($buyRemain, $sellRemain, 8) <= 0 ? $buyRemain : $sellRemain; //本次交易量

                        $buyOrder->filled_amount = bcadd($buyOrder->filled_amount, $tradeAmount, 8);
                        $sellOrder->filled_amount = bcadd($sellOrder->filled_amount, $tradeAmount, 8);

                        // 若买家或卖家的订单完全成交，修改订单状态
                        if (bccomp($buyOrder->filled_amount, $buyOrder->amount, 8) >= 0) {
                            $buyOrder->status = TradeConstants::STATUS_FILLED;
                        }else{
                            $buyOrder->status = TradeConstants::STATUS_PARTIAL;
                        }

                        if (bccomp($sellOrder->filled_amount, $sellOrder->amount, 8) >= 0) {
                            $sellOrder->status = TradeConstants::STATUS_FILLED;
                        }else{
                            $sellOrder->status = TradeConstants::STATUS_PARTIAL;
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
