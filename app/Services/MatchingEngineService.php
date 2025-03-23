<?php
namespace app\Services;

use App\Constants\TradeConstants;
use App\Models\AssetLedgerModel;
use App\Models\OrderModel;
use App\Models\TradeModel;
use App\Models\TradingPairModel;
use App\Models\UserModel;

/**
 * 批量交易对订单撮合引擎实现
 *
 * 该类实现了批量执行所有交易对（BTC_USDT 和 ETH_USDT）的订单撮合逻辑，
 * 支持市价买单、限价买单、限价卖单与市价卖单之间的撮合规则，
 * 并遵循价格/时间优先原则，同时使用高精度数学函数处理价格和数量计算。
 *
 * @author artisan
 * @email  g1090045743@gmail.com
 * @since  2025年03月23日23:52
 */
class MatchingEngineService {

    /**
     * 撮合所有活跃交易对（仅处理 BTC_USDT 和 ETH_USDT）
     *
     * 此方法固定了两个交易对，分别为 BTC_USDT 和 ETH_USDT，
     * 然后依次调用 matchOrders() 方法对每个交易对进行撮合。
     *
     * @throws \Exception
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日23:52
     */
    public function matchAllPairs() {
        // 固定交易对列表：BTC_USDT 和 ETH_USDT
        $pairList = ['BTC_USDT', 'ETH_USDT'];

        // 遍历每个交易对，并执行撮合逻辑
        foreach ($pairList as $pairId) {
            $this->matchOrders($pairId);
        }
        // logger()->write("撮合脚本执行完毕", 'info');
    }

    /**
     * 撮合指定交易对的订单
     *
     * 该方法负责对指定交易对（$pairId）的所有买单和卖单进行撮合。
     * 它会进行订单验证、计算成交价格和成交数量，
     * 更新订单状态，记录成交记录并更新用户资产。
     *
     * @param string $pairId 交易对ID
     * @throws \Exception 当撮合过程中出现异常时，抛出异常并回滚数据库事务。
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月24日00:32
     */
    public function matchOrders($pairId) {
        $db = db();
        $orderModel = new OrderModel();
        $tradeModel = new TradeModel();

        try {
            // 开启数据库事务
            $db->begin();

            // 查询当前交易对的所有买单，按照市价优先，价格高优先排序
            $buyOrders = $orderModel->getOpenBuyOrders($pairId);
            // 查询当前交易对的所有卖单，按照市价优先，价格低优先排序
            $sellOrders = $orderModel->getOpenSellOrders($pairId);
            if (count($buyOrders) > 0 || count($sellOrders) > 0)  {
                logger()->write("开始撮合交易对：$pairId", 'info');
            }
            // 遍历所有买单
            foreach ($buyOrders as $buyOrder) {
                // 对每个买单遍历所有卖单
                foreach ($sellOrders as $sellOrder) {
                    // 排除同一用户的订单（防止自己与自己撮合）
                    if ($buyOrder->user_id == $sellOrder->user_id) {
                        continue;
                    }
                    // 如果两个订单都是市价单，则不撮合
                    if ($buyOrder->type == TradeConstants::TYPE_MARKET && $sellOrder->type == TradeConstants::TYPE_MARKET) {
                        continue;
                    }
                    // 如果两个订单都是限价单，则只有当买单价格大于等于卖单价格时才撮合
                    if ($buyOrder->type == TradeConstants::TYPE_LIMIT && $sellOrder->type == TradeConstants::TYPE_LIMIT) {
                        if (bccomp($buyOrder->price, $sellOrder->price, 8) < 0) {
                            continue;
                        }
                    }

                    // 计算买单和卖单剩余数量
                    $buyRemain = bcsub($buyOrder->amount, $buyOrder->filled_amount, 8);
                    $sellRemain = bcsub($sellOrder->amount, $sellOrder->filled_amount, 8);
                    if (bccomp($buyRemain, '0', 8) == 0 || bccomp($sellRemain, '0', 8) == 0) {
                        continue;
                    }

                    // 确定成交价格，默认采用卖单价格
                    $tradePrice = $sellOrder->price;
                    if ($buyOrder->type == TradeConstants::TYPE_LIMIT && $sellOrder->type == TradeConstants::TYPE_MARKET) {
                        $tradePrice = $buyOrder->price;
                    }
                    // 成交数量为两订单剩余数量中的较小值
                    $tradeAmount = bccomp($buyRemain, $sellRemain, 8) <= 0 ? $buyRemain : $sellRemain;
                    if (bccomp($tradeAmount, '0', 8) == 0) {
                        continue;
                    }

                    // 保存撮合前的已成交数量，便于记录本次撮合信息
                    $prevBuyFilled = $buyOrder->filled_amount;
                    $prevSellFilled = $sellOrder->filled_amount;

                    // 更新订单成交数量
                    $buyOrder->filled_amount = bcadd($buyOrder->filled_amount, $tradeAmount, 8);
                    $sellOrder->filled_amount = bcadd($sellOrder->filled_amount, $tradeAmount, 8);

                    // 更新订单状态
                    $buyOrder->status = bccomp($buyOrder->filled_amount, $buyOrder->amount, 8) >= 0
                        ? TradeConstants::STATUS_FILLED
                        : TradeConstants::STATUS_PARTIAL;
                    $sellOrder->status = bccomp($sellOrder->filled_amount, $sellOrder->amount, 8) >= 0
                        ? TradeConstants::STATUS_FILLED
                        : TradeConstants::STATUS_PARTIAL;

                    // 保存订单更新
                    $buyOrder->save();
                    $sellOrder->save();

                    // 计算本次撮合后的剩余数量
                    $newBuyRemain = bcsub($buyOrder->amount, $buyOrder->filled_amount, 8);
                    $newSellRemain = bcsub($sellOrder->amount, $sellOrder->filled_amount, 8);

                    // 输出买单详细信息日志（本次撮合前后的数据）
                    $buyMsg = "买单 #订单号{$buyOrder->order_id} (买家用户：{$buyOrder->user_id}) 下单数量：{$buyOrder->amount}，"
                        . "原已成交：{$prevBuyFilled}，本次撮合：{$tradeAmount}，现已成交：{$buyOrder->filled_amount}，剩余：{$newBuyRemain}。";
                    $buyMsg .= ($buyOrder->status == TradeConstants::STATUS_FILLED) ? " 订单已完全成交" : " 订单部分成交";
                    logger()->write($buyMsg, 'info');

                    // 如果买单分批匹配后已完全成交，则输出额外日志
                    if (bccomp($newBuyRemain, '0', 8) == 0 && $buyOrder->status == TradeConstants::STATUS_FILLED && bccomp($prevBuyFilled, $buyOrder->amount, 8) < 0) {
                        logger()->write("买单 #订单号{$buyOrder->order_id} (买家用户：{$buyOrder->user_id}) 分批撮合完成，当前订单状态已改为完全成交。", 'info');
                    }

                    // 输出卖单详细信息日志
                    $sellMsg = "卖单 #订单号{$sellOrder->order_id} (卖家用户：{$sellOrder->user_id}) 下单数量：{$sellOrder->amount}，"
                        . "原已成交：{$prevSellFilled}，本次撮合：{$tradeAmount}，现已成交：{$sellOrder->filled_amount}，剩余：{$newSellRemain}。";
                    $sellMsg .= ($sellOrder->status == TradeConstants::STATUS_FILLED) ? " 订单已完全成交" : " 订单部分成交";
                    logger()->write($sellMsg, 'info');

                    // 如果卖单分批匹配后已完全成交，则输出额外日志
                    if (bccomp($newSellRemain, '0', 8) == 0 && $sellOrder->status == TradeConstants::STATUS_FILLED && bccomp($prevSellFilled, $sellOrder->amount, 8) < 0) {
                        logger()->write("卖单 #订单号{$sellOrder->order_id} (卖家用户：{$sellOrder->user_id}) 分批撮合完成，当前订单状态已改为完全成交。", 'info');
                    }

                    // 创建撮合成交记录
                    $tradeModel->createTrade(
                        $buyOrder->order_id,
                        $sellOrder->order_id,
                        $pairId,
                        $tradePrice,
                        $tradeAmount,
                        0
                    );

                    // 输出撮合成交详细日志
                    $tradeLog = "撮合成交：买单 #订单号{$buyOrder->order_id} (买家：{$buyOrder->user_id}, 下单数量：{$buyOrder->amount}, 本次撮合：{$tradeAmount}) "
                        . "与卖单 #订单号{$sellOrder->order_id} (卖家：{$sellOrder->user_id}, 下单数量：{$sellOrder->amount}, 本次撮合：{$tradeAmount}) 成交，成交价：{$tradePrice}。";
                    logger()->write($tradeLog, 'info');

                    // 更新成交后的用户资产
                    $this->updateUserAssets(
                        $buyOrder->user_id,
                        $sellOrder->user_id,
                        $pairId,
                        $tradeAmount,
                        $tradePrice,
                        $buyOrder->order_id,
                        $sellOrder->order_id
                    );

                    // 如果买单已完全成交，则退出内层循环，继续处理下一个买单
                    $buyRemain = bcsub($buyOrder->amount, $buyOrder->filled_amount, 8);
                    if (bccomp($buyRemain, '0', 8) == 0) {
                        break;
                    }
                }
            }

            // 提交事务
            $db->commit();
        } catch (\Exception $e) {
            // 出现异常时回滚事务
            $db->rollback();
            logger()->write("撮合失败：" . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * 更新用户余额、写入资产流水
     *
     * 此方法根据撮合后的成交记录，更新买家和卖家的资产余额，
     * 包括释放锁定的资金、增加对应资产的可用余额，
     * 并写入资产流水记录。
     *
     * @param int    $buyerId    买家用户ID
     * @param int    $sellerId   卖家用户ID
     * @param string $pairId     交易对ID
     * @param string $amount     成交数量
     * @param string $price      成交价格
     * @param int    $buyOrderId 买单订单ID
     * @param int    $sellOrderId 卖单订单ID
     * @throws \Exception
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日23:52
     */
    private function updateUserAssets(int $buyerId, int $sellerId, string $pairId, string $amount, string $price, int $buyOrderId, int $sellOrderId) {
        $assetLedgerModel = new AssetLedgerModel();
        $tradingPairModel = new TradingPairModel();
        $userModel = new UserModel();
        // 获取交易对信息（基础币和计价币）
        $pair = $tradingPairModel->findById($pairId);
        $base = $pair['base'];
        $quote = $pair['quote'];

        // 计算成交总额（计价币数量）
        $quoteTotal = bcmul($amount, $price, 8);
        // 计算资产变动负值
        $negBase = bcmul($amount, '-1', 8);
        $negQuote = bcmul($quoteTotal, '-1', 8);

        // 买家：释放锁定的计价币并增加基础币
        $userModel->releaseLockedBalance($buyerId, $quote, $quoteTotal);
        $userModel->increaseAvailableBalance($buyerId, $base, $amount);

        // 卖家：释放锁定的基础币并增加计价币
        $userModel->releaseLockedBalance($sellerId, $base, $amount);
        $userModel->increaseAvailableBalance($sellerId, $quote, $quoteTotal);

        // 写入资产流水记录
        $assetLedgerModel->createLedger($buyerId, $quote, $negQuote, 2, $buyOrderId);
        $assetLedgerModel->createLedger($buyerId, $base, $amount, 2, $buyOrderId);
        $assetLedgerModel->createLedger($sellerId, $quote, $quoteTotal, 2, $sellOrderId);
        $assetLedgerModel->createLedger($sellerId, $base, $negBase, 2, $sellOrderId);
    }
}
