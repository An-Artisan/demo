<?php

namespace app\Services;

use app\Models\AssetLedgerModel;
use App\Models\OrderModel;
use app\Models\TradingPairModel;
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

        try {
            $this->db->begin();

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
     */
    public static function calculateMarketBuyCost(string $pairId, string $amount, string $bufferRate = '1.01'): array
    {
        $asks = self::getAsks($pairId);
        $remaining = $amount;
        $cost = '0';
        $usedAsks = [];

        foreach ($asks as $ask) {
            $askPrice = $ask['price'];
            $askVolume = $ask['amount'];
            $fill = bccomp($remaining, $askVolume, 8) > 0 ? $askVolume : $remaining;
            $costPart = bcmul($fill, $askPrice, 8);
            $cost = bcadd($cost, $costPart, 8);
            $usedAsks[] = [
                'price' => $askPrice,
                'amount' => $fill,
                'cost' => $costPart
            ];
            $remaining = bcsub($remaining, $fill, 8);
            if (bccomp($remaining, '0', 8) <= 0) break;
        }

        if (bccomp($remaining, '0', 8) > 0) {
            return ['success' => false, 'message' => 'Insufficient depth to fulfill market buy'];
        }

        $bufferedCost = bcmul($cost, $bufferRate, 8);

        return [
            'success' => true,
            'cost' => $cost,
            'buffered_cost' => $bufferedCost,
            'used_asks' => $usedAsks,
            'locked_balance' => $bufferedCost
        ];
    }


}
