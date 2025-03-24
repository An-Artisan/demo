<?php

namespace app\Console\Commands;

use app\Models\OrderModel;
use app\Models\TradeModel;
use app\Models\UserModel;
use app\Models\AssetLedgerModel;
use app\Constants\TradeConstants;

class SyncRemainingJob
{

    // php artisan SyncRemainingJob run
    protected $f3;

    public function __construct($f3)
    {
        $this->f3 = $f3;
    }

    public function handle()
    {
        echo "这是默认的任务处理方法\n";
    }

    public function run()
    {
        while (true) {
            $orderModel = new OrderModel();
            $tradeModel = new TradeModel();
            $userModel = new UserModel();
            $assetLedgerModel = new AssetLedgerModel();

            // 查询所有完全成交的市场买单，且 lock_amount 不为空（仅针对买单 side=0，type=1）
            $orders = $orderModel->find(["status = ? AND type = ? AND side = ? AND lock_amount IS NOT NULL",
                TradeConstants::STATUS_FILLED,
                TradeConstants::TYPE_MARKET,
                TradeConstants::SIDE_BUY
            ]);

            foreach ($orders as $order) {
                // 计算实际使用的资金：从 Trade 表中统计该订单相关成交记录
                // 关联条件：订单ID 等于 taker_order_id 或 maker_order_id
                $sql = "SELECT IFNULL(SUM(price * amount), '0.00000000') as usedFunds
                    FROM trades
                    WHERE taker_order_id = ? OR maker_order_id = ?";
                $result = db()->exec($sql, [$order->order_id, $order->order_id]);
                $usedFunds = $result[0]['usedFunds'];

                // 计算剩余未使用的锁定金额 = lock_amount - usedFunds
                $remaining = bcsub($order->lock_amount, $usedFunds, 8);
                // 如果剩余大于0，则释放剩余锁定金额
                if (bccomp($remaining, '0', 8) === 1) {
                    // 获取交易对信息，假设订单的 pair_id 格式为 "BTC_USDT"
                    $pairParts = explode('_', $order->pair_id);
                    if (count($pairParts) != 2) {
                        // 格式不正确则跳过
                        continue;
                    }
                    // 对于买单，锁定的是计价币，即 pairParts[1]
                    $quote = $pairParts[1];
                    try {
                        // 释放剩余锁定余额
                        $userModel->releaseLockedBalance($order->user_id, $quote, $remaining);

                        // 记录资产流水，流水类型为4 表示释放锁定
                        $assetLedgerModel->createLedger(
                            $order->user_id,
                            $quote,
                            $remaining, // 正数表示释放/增加可用余额
                            4,          // 流水类型4：释放锁定余额
                            $order->order_id
                        );

                        logger()->write("订单 #{$order->order_id} 市场买单完全成交，已释放剩余锁定金额：{$remaining} {$quote}", 'info');
                    } catch (\Exception $e) {
                        logger()->write("订单 #{$order->order_id} 释放锁定余额失败：" . $e->getMessage(), 'error');
                    }
                }
            }
        }
        sleep(1);
    }
}
