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
class MatchingEngineService
{

    protected $orderModel;
    protected $tradeModel;
    protected $userModel;
    protected $tradingPairModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->tradeModel = new TradeModel();
        $this->userModel = new UserModel();
        $this->tradingPairModel = new TradingPairModel();
    }

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
    public function matchAllPairs()
    {
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
    public function matchOrders(string $pairId)
    {
        $db = db();

        try {
            // 开启数据库事务（确保每个交易对撮合的事务尽量短，以免长事务影响 MySQL 性能）
            $db->begin();

            $buyOrders = $this->orderModel->getOpenBuyOrders($pairId);
            $sellOrders = $this->orderModel->getOpenSellOrders($pairId);

            foreach ($buyOrders as $buyOrder) {
                foreach ($sellOrders as $sellOrder) {
                    // 排除同一用户的订单
                    if ($buyOrder->user_id == $sellOrder->user_id) {
                        continue;
                    }
                    // 跳过双方均为市价单的情况
                    if ($buyOrder->type == TradeConstants::TYPE_MARKET && $sellOrder->type == TradeConstants::TYPE_MARKET) {
                        continue;
                    }
                    // 限价 vs 限价时，只有当买价>=卖价才允许撮合
                    if ($buyOrder->type == TradeConstants::TYPE_LIMIT && $sellOrder->type == TradeConstants::TYPE_LIMIT) {
                        if (bccomp($buyOrder->price, $sellOrder->price, 8) < 0) {
                            continue;
                        }
                    }

                    // 获取剩余可成交数量
                    $buyRemain = $this->getRemain($buyOrder);
                    $sellRemain = $this->getRemain($sellOrder);
                    if (bccomp($buyRemain, '0', 8) == 0 || bccomp($sellRemain, '0', 8) == 0) {
                        continue;
                    }

                    // 默认采用卖单价格
                    $tradePrice = $sellOrder->price;
                    // 当买单为限价单且卖单为市价单时，成交价取买单价格
                    if ($buyOrder->type == TradeConstants::TYPE_LIMIT && $sellOrder->type == TradeConstants::TYPE_MARKET) {
                        $tradePrice = $buyOrder->price;
                    }
                    $tradeAmount = (bccomp($buyRemain, $sellRemain, 8) <= 0) ? $buyRemain : $sellRemain;
                    if (bccomp($tradeAmount, '0', 8) == 0) {
                        continue;
                    }

                    // 保存撮合前的成交数据，便于记录日志
                    $prevBuyFilled = $buyOrder->filled_amount;
                    $prevSellFilled = $sellOrder->filled_amount;

                    // 更新买单和卖单
                    $this->updateOrderFilled($buyOrder, $tradeAmount);
                    $this->updateOrderFilled($sellOrder, $tradeAmount);

                    $newBuyRemain = $this->getRemain($buyOrder);
                    $newSellRemain = $this->getRemain($sellOrder);

                    // 输出订单详细日志
                    $this->logOrderDetails($buyOrder, $prevBuyFilled, $tradeAmount, $newBuyRemain, "买单");
                    $this->logOrderDetails($sellOrder, $prevSellFilled, $tradeAmount, $newSellRemain, "卖单");

                    // 生成撮合成交记录
                    $this->tradeModel->createTrade(
                        $buyOrder->order_id,
                        $sellOrder->order_id,
                        $pairId,
                        $tradePrice,
                        $tradeAmount,
                        0
                    );

                    // 输出撮合成交日志
                    $tradeLog = "撮合成交：买单 #订单号{$buyOrder->order_id} (买家：{$buyOrder->user_id}, 下单数量：{$buyOrder->amount}, 本次撮合：{$tradeAmount}) "
                        . "与卖单 #订单号{$sellOrder->order_id} (卖家：{$sellOrder->user_id}, 下单数量：{$sellOrder->amount}, 本次撮合：{$tradeAmount}) 成交，成交价：{$tradePrice}。";
                    logger()->write($tradeLog, 'info');

                    // 更新用户资产
                    $this->updateUserAssets(
                        $buyOrder->user_id,
                        $sellOrder->user_id,
                        $pairId,
                        $tradeAmount,
                        $tradePrice,
                        $buyOrder->order_id,
                        $sellOrder->order_id
                    );

                    // 如果买单已完全成交，则退出内层循环
                    $buyRemain = $this->getRemain($buyOrder);
                    if (bccomp($buyRemain, '0', 8) == 0) {
                        break;
                    }
                }
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            logger()->write("撮合失败：" . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * 计算订单剩余未成交数量
     *
     * @param  $order
     * @return string 剩余数量，精度8位小数
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日23:52
     */
    private function getRemain($order): string
    {
        return bcsub($order->amount, $order->filled_amount, 8);
    }

    /**
     * 更新订单的成交数量与状态，并保存
     *
     * @param  $order
     * @param string $tradeAmount 本次撮合成交数量
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日23:52
     */
    private function updateOrderFilled($order, string $tradeAmount)
    {
        $order->filled_amount = bcadd($order->filled_amount, $tradeAmount, 8);
        $order->status = (bccomp($order->filled_amount, $order->amount, 8) >= 0)
            ? TradeConstants::STATUS_FILLED
            : TradeConstants::STATUS_PARTIAL;
        $order->save();
    }

    /**
     * 输出订单撮合前后详细日志，并在订单分批撮合完成时输出额外提示
     *
     * @param  $order
     * @param string $prevFilled 撮合前已成交数量
     * @param string $tradeAmount 本次撮合数量
     * @param string $newRemain 撮合后剩余数量
     * @param string $orderTypeDesc 订单类型描述（例如“买单”或“卖单”）
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日23:52
     */
    private function logOrderDetails($order, string $prevFilled, string $tradeAmount, string $newRemain, string $orderTypeDesc)
    {
        $msg = "{$orderTypeDesc} #订单号{$order->order_id} (用户：{$order->user_id}) 下单数量：{$order->amount}，"
            . "原已成交：{$prevFilled}，本次撮合：{$tradeAmount}，现已成交：{$order->filled_amount}，剩余：{$newRemain}。";
        $msg .= ($order->status == TradeConstants::STATUS_FILLED) ? " 订单已完全成交" : " 订单部分成交";
        logger()->write($msg, 'info');

        // 如果订单分批撮合后已完全成交，则输出额外日志
        if (bccomp($newRemain, '0', 8) == 0 && $order->status == TradeConstants::STATUS_FILLED && bccomp($prevFilled, $order->amount, 8) < 0) {
            logger()->write("{$orderTypeDesc} #订单号{$order->order_id} (用户：{$order->user_id}) 分批撮合完成，当前订单状态已改为完全成交。", 'info');
        }
    }

    /**
     * 更新用户余额、写入资产流水
     *
     * 此方法根据撮合后的成交记录，更新买家和卖家的资产余额，
     * 包括释放锁定的资金、增加对应资产的可用余额，
     * 并写入资产流水记录。
     *
     * @param int $buyerId 买家用户ID
     * @param int $sellerId 卖家用户ID
     * @param string $pairId 交易对ID
     * @param string $amount 成交数量
     * @param string $price 成交价格
     * @param int $buyOrderId 买单订单ID
     * @param int $sellOrderId 卖单订单ID
     * @throws \Exception
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日23:52
     */
    private function updateUserAssets(int $buyerId, int $sellerId, string $pairId, string $amount, string $price, int $buyOrderId, int $sellOrderId)
    {
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
