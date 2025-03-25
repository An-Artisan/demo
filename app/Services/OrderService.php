<?php

namespace app\Services;

use app\Models\AssetLedgerModel;
use App\Models\OrderModel;
use app\Models\UserModel;


class OrderService
{
    protected $OrderModel;
    protected $UserModel;
    protected $AssetLedgerModel;
    protected $db;

    public function __construct()
    {
        $this->OrderModel = new OrderModel();
        $this->UserModel = new UserModel();
        $this->AssetLedgerModel = new AssetLedgerModel();
        $this->db = db();
    }

    public function createOrder($order, $checkResult)
    {
        if (empty($order)) {
            logger()->write("Empty data passed to createOrder()", 'error');
            return false;
        }
        $this->db->begin();
        try {
            // 创建订单
            $orderId = $this->OrderModel->createOrder($order);
            if (!$orderId) {
                throw new \Exception("订单创建失败");
            }

            // 扣除余额逻辑
            $lockedBalance = $checkResult['marketBuyCost']['locked_balance'] ?? null;
            $currency = $checkResult['marketBuyCost']['currency'] ?? null;
            if ($lockedBalance && $currency) {
                $this->UserModel->deductBalance($order['user_id'], $currency, $lockedBalance);

                // 添加资产流水记录
                $this->AssetLedgerModel->createLedger(
                    $order['user_id'],
                    strtoupper($currency),
                    -1 * floatval($lockedBalance),
                    3, // 3-冻结
                    $orderId
                );
            } else {
                throw new \Exception("订单创建失败");
            }

            $this->db->commit();
            return $orderId;

        } catch (\Exception $e) {
            $this->db->rollback();
            logger()->write("createOrder事务失败: " . $e->getMessage(), 'error');
            return false;
        }
    }

    public function cancelOrder($orderId)
    {
        if (empty($orderId)) {
            logger()->write("Empty data passed to cancelOrder()", 'error');
            return false;
        }
        // 取消订单
        $result = $this->OrderModel->cancelOrder($orderId);
        // 返回结果
        return $result;
    }

    public function findOrderById($orderId)
    {
        return $this->OrderModel->findById($orderId);
    }


    public function findCurrentOrders($userId, $pairId)
    {
        return $this->OrderModel->findCurrentOrders($userId, $pairId);
    }

    public function findCurrentOrdersAll($pairId, $limit)
    {
        return $this->OrderModel->findCurrentOrdersAll($pairId, $limit);
    }


    public function countCurrentOrders($userId)
    {
        return $this->OrderModel->countCurrentOrders($userId);
    }

    public function findHistoryOrders($userId, $pairId)
    {
        return $this->OrderModel->findHistoryOrders($userId, $pairId);
    }

    public function countHistoryOrders($userId)
    {
        return $this->OrderModel->countHistoryOrders($userId);
    }

    public function findFilledOrders($userId, $pairId)
    {
        return $this->OrderModel->findFilledOrders($userId, $pairId);
    }

    public function countFilledOrders($userId)
    {

        return $this->OrderModel->countFilledOrders($userId);
    }

    /**
     * 获取某币种（交易对）下最近成交的N条记录
     */
    public function getRecentTradesByPair($pairId, $limit = 10)
    {
        return $this->OrderModel->findRecentByPair($pairId, $limit);
    }


    /**
     * 获取当前卖盘深度（asks）
     * 仅返回 status = 0（未成交） 或 1（部分成交） 的挂单，按价格升序排列
     *
     * @param string $pairId
     * @param int $limit 获取的最大深度条数，默认10
     * @return array 返回格式：[ ['price' => 'xxx', 'amount' => 'xxx'], ... ]
     * @author artisan
     */
    public static function getAsks(string $pairId, int $limit = 10): array
    {
        $db = db();

        $result = $db->exec(
            "SELECT price, (amount - filled_amount) AS amount
             FROM orders
             WHERE pair_id = ?
               AND side = 1 -- 卖出单
               AND type = 0
               AND status IN (0, 1)
               AND (amount - filled_amount) > 0
             ORDER BY price ASC
             LIMIT ?",
            [$pairId, $limit]
        );

        // 格式化为纯字符串（防止浮点问题）
        $asks = array_map(function ($row) {
            return [
                'price' => (string)$row['price'],
                'amount' => (string)$row['amount'],
            ];
        }, $result);

        return $asks;
    }

    public static function getBids(string $pairId, int $limit = 10): array
    {
        $db = db();

        $result = $db->exec(
            "SELECT price, (amount - filled_amount) AS amount
             FROM orders
             WHERE pair_id = ?
               AND side = 0 -- 买入单
               AND type = 0
               AND status IN (0, 1)
               AND (amount - filled_amount) > 0
             ORDER BY price DESC
             LIMIT ?",
            [$pairId, $limit]
        );

        // 格式化为纯字符串（防止浮点问题）
        $bids = array_map(function ($row) {
            return [
                'price' => (string)$row['price'],
                'amount' => (string)$row['amount'],
            ];
        }, $result);

        return $bids;
    }

    /**
     * 根据买入数量，动态吃深度，计算市价买单所需 USDT
     *
     * @param string $pairId
     * @param string $amount 计划买入的 BTC 数量
     * @param string $bufferRate 默认 1.01（1% buffer）
     * @return array [
     *     'success' => true,
     *     'cost' => string,
     *     'buffered_cost' => string,
     *     'used_asks' => array,
     *     'locked_balance' => string
     * ]
     * @author liuqiang
     * @email g1090035743@gmail.com
     */
    public static function calculateMarketBuyCost( string $amount, $rate,string $bufferRate = '1.01'): array
    {

        // 计算 buffer：多预留出 bufferRate% 的资金来冻结，避免撮合失败
        // 例如：bufferRate = 1.01，表示加 1%
        $marketBuyCost= bcmul($amount, $rate, 8);
        $buffer = bcmul($marketBuyCost,  $bufferRate,8);
        return [
            'success' => true,
            'locked_balance' => $buffer,      // 实际需要冻结的资金
        ];
    }


    // 市价卖单：卖出 X 个币，系统从买盘（bids）里依次成交
    public static function calculateMarketSellIncome(string $pairId, string $amount): array
    {
//        // 获取当前交易对的买盘深度（bids）
//        $bids = OrderService::getBids($pairId);
//        // 如果买盘为空，说明市场没人买，不能成交
//        if (empty($bids)) {
//            return ['success' => false, 'message' => '市场无买盘深度，无法成交'];
//        }
//
//        $remaining = $amount;  // 用户还希望卖出的数量
//        $income = '0';         // 累计卖出获得的总收入
//        $usedBids = [];        // 实际吃掉的买盘记录
//
//        // 遍历买盘，按从高到低价格吃单（价格越高越优先）
//        foreach ($bids as $bid) {
//            $bidPrice = $bid['price'];         // 当前买盘价格
//            $bidVolume = $bid['amount'];       // 当前买盘可买数量
//
//            // 当前轮最多卖多少：min(买盘量, 剩余可卖)
//            $fill = bccomp($remaining, $bidVolume, 8) > 0 ? $bidVolume : $remaining;
//
//            // 当前轮卖出获得的收入 = 数量 * 单价
//            $incomePart = bcmul($fill, $bidPrice, 8);
//
//            // 累加总收入
//            $income = bcadd($income, $incomePart, 8);
//
//            // 记录本次吃单记录
//            $usedBids[] = [
//                'price' => $bidPrice,
//                'amount' => $fill,
//                'income' => $incomePart
//            ];
//
//            // 更新剩余未成交量
//            $remaining = bcsub($remaining, $fill, 8);
//
//            // 如果卖完了就退出循环
//            if (bccomp($remaining, '0', 8) <= 0) break;
//        }
//
//        // 实际成交数量 = 用户原始卖出量 - 剩余未成交量
//        $filledAmount = bcsub($amount, $remaining, 8);
//
//        // 如果一个都没成交，拒绝下单
//        if (bccomp($filledAmount, '0', 8) <= 0 || bccomp($income, '0', 8) <= 0) {
//            return ['success' => false, 'message' => '市场买盘不足，无法完成任何成交'];
//        }

        return [
            'success' => true,
//            'partial_filled' => bccomp($remaining, '0', 8) > 0, // 是否部分成交
//            'income' => $income,                    // 总共获得的收入
            'locked_balance' => $amount,      // 实际需要冻结的币数量
//            'filled_amount' => $filledAmount,       // 实际卖出数量
//            'used_bids' => $usedBids                // 撮合路径记录
        ];
    }
}
