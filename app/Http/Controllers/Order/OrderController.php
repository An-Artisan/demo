<?php

namespace app\Http\Controllers\Order;

use app\Http\Controllers\BaseController;
use app\Models\AssetLedgerModel;
use app\Models\OrderModel;
use app\Models\TradingPairModel;
use app\Models\UserModel;
use app\Services\MatchingEngineService;
use app\Services\TradingPairService;
use app\Services\UserService;
use Base;
use app\Constants\TradeConstants;
use app\Services\OrderService;
use lib\gate\GateClient;

class OrderController extends BaseController
{
    /**
     *
     * @param $f3
     * @author artisan
     * @email  g1090035743@gmail.copm
     * @since  2025年03月23日19:00
     */
    public function createOrder($f3)
    {
        $userId = get_current_uid();
        $body = json_decode($f3->get('BODY'), true);
        $pairId = $body['pair_id'] ?? 'BTC_USDT';
        $type = $body['type'] ?? 0;
        $side = $body['side'] ?? 0;
        $price = $body['price'] ?? 0;
        $amount = $body['amount'] ?? 0;

        if (!$userId)  $this->error(400, 'User not logged in');
        if (!$pairId)  $this->error(400, 'Invalid input pairId');
        if (bccomp($amount, 0, 8) <= 0)  $this->error(400, 'Invalid amount');
        if ($type == TradeConstants::TYPE_LIMIT && bccomp($price, 0, 8) <= 0)  $this->error(400, 'Invalid price');

        $TradingPairService = new TradingPairService();
        $tradingPair = $TradingPairService->findById($pairId);
        if (!$tradingPair)  $this->error(400, 'Invalid trading pair');

        $UserService = new UserService();
        $user = $UserService->getUserById($userId);
        if (!$user)  $this->error(400, 'Invalid user');

        // 获取用户余额
        $balances = $UserService->getUserSpotBalances($user);
        /**
         * 获取卖盘盘口（asks）
         *
         * 动态计算所需 USDT
         *
         * 校验你的余额是否足够
         *
         * （支持吃深度） 深度默认为 10
         *
         *  支持buffer配置，默认再多预留 1%
         */
        $checkResult = $UserService->checkUserBalanceForOrder($pairId, $type, $side, $price, $amount, $balances);
        if (!$checkResult['success'])  $this->error(400, $checkResult['message']);

        // 构建订单结构
        $order = [
            'user_id' => $userId,
            'pair_id' => $pairId,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount
        ];

        $OrderService = new OrderService();
        $order_id = $OrderService->createOrder($order,$checkResult);

        if ($order_id) {
            $this->success(['order_id' => $order_id], 'Order placed successfully');
        } else {
            $this->error(500, 'Failed to place order');
        }
    }

    // 用户撤销订单
    public function cancelOrder($f3)
    {
        $userId = get_current_uid();
        $body = json_decode($f3->get('BODY'), true);
        $orderId = $body['order_id'] ?? 0;

        if (!$userId) {
            $this->error(400, 'User not logged in');
            return;
        }

        if (!$orderId) {
            $this->error(400, 'Invalid order ID');
            return;
        }

        $OrderService = new OrderService();
        $order = $OrderService->findOrderById($orderId);

        if (!$order) {
            $this->error(400, 'Invalid order ID');
            return;
        }

        if ($order->user_id != $userId) {
            $this->error(403, 'Unauthorized to cancel this order');
            return;
        }

        if ($order->status == TradeConstants::STATUS_FILLED) {
            $this->error(400, 'Order already filled');
            return;
        }

        // 取消订单
        $result = $OrderService->cancelOrder($orderId);
        if ($result) {
            // 计算订单剩余未成交数量
            $remaining = bcsub($order->amount, $order->filled_amount, 8);

            // 解析交易对，例如 "BTC_USDT"
            $pairParts = explode('_', $order->pair_id);
            if (count($pairParts) != 2) {
                $this->error(400, 'Invalid pair format');
                return;
            }

            // 判断订单类型，计算释放的锁定余额
            if ($order->side == TradeConstants::SIDE_BUY) {
                // 买单锁定的是计价币（第二部分），释放金额 = 剩余数量 * 限价单价格
                $currencyToRelease = $pairParts[1];
                $releaseAmount = bcmul($remaining, $order->price, 8);
            } else {
                // 卖单锁定的是基础币（第一部分）
                $currencyToRelease = $pairParts[0];
                $releaseAmount = $remaining;
            }

            // 调用用户模型方法释放锁定余额
            $UserModel = new UserModel();
            $assetLedgerModel = new AssetLedgerModel();
            try {
                $UserModel->releaseLockedBalance($userId, $currencyToRelease, $releaseAmount);
                // 此处流水类型使用4表示解冻（或资产调整），关联订单为撤单订单ID
                $assetLedgerModel->createLedger($userId, $currencyToRelease, $releaseAmount, 2, $orderId);
            } catch (\Exception $e) {
                // 如果释放锁定余额失败，则记录日志并返回错误
                logger()->write("释放锁定余额失败：" . $e->getMessage(), 'error');
                $this->error(500, 'Failed to release locked balance');
                return;
            }

            $this->success([], 'Order cancelled successfully');
        } else {
            $this->error(500, 'Failed to cancel order');
        }
    }

    // 获取用户当前委托列表（带分页）
    public function getCurrentOrderList($f3)
    {
        // 获取用户ID
        $userId = get_current_uid();
        $pairId = $f3->get('GET.pair_id');
        // 查询用户当前委托（未完全成交的订单）
        $OrderService = new OrderService();
        $orders = $OrderService->findCurrentOrders($userId, $pairId);
        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'order_id' => $order->order_id,
                'pair_id' => $order->pair_id,
                'type' => $order->type,
                'side' => $order->side,
                'price' => format_number($order->price),
                'amount' => $order->amount,
                'filled_amount' => $order->filled_amount,
                'status' => $order->status,
                'created_at' => $order->created_at
            ];
        }

        // 返回结果
        $this->success($result);
    }

    public function getCurrentOrderListLocal($f3)
    {
        $pairId = $f3->get('GET.pair_id');

        // 获取用户ID
        $userId = get_current_uid();

        // 查询用户当前委托（未完全成交的订单）
        $OrderService = new OrderService();
        $orders = $OrderService->findCurrentOrdersAll($pairId, 10);
        $asks = [];
        $bids = [];

        foreach ($orders as $order) {
            $price = (string)$order->price;
            $amount = bcsub($order->amount, $order->filled_amount, 8);

            if ($amount <= 0) {
                continue;
            }

            if ($order->side == 1) {
                // 卖出 -> ask
                $asks[$price] = isset($asks[$price]) ? bcadd($asks[$price], $amount, 8) : $amount;
            } else {
                // 买入 -> bid
                $bids[$price] = isset($bids[$price]) ? bcadd($bids[$price], $amount, 8) : $amount;
            }
        }

        // 排序价格
        ksort($asks, SORT_NUMERIC);       // 卖单从低到高
        krsort($bids, SORT_NUMERIC);      // 买单从高到低

        // 转换为数组格式
        $askArr = [];
        $bidArr = [];

        foreach ($asks as $price => $amount) {
            $askArr[] = [format_number($price), format_number($amount)];
        }
        foreach ($bids as $price => $amount) {
            $bidArr[] = [format_number($price), format_number($amount)];
        }

        // 返回数据结构
        $result = [
            'current' => time() . rand(100, 999),
            'update' => time() . rand(100, 999),
            'asks' => $askArr,
            'bids' => $bidArr
        ];

        $this->success($result);
    }

    // 获取用户历史委托列表
    public function getHistoryOrderList($f3)
    {
        // 获取用户ID
        $userId = get_current_uid();
        $pairId = $f3->get('GET.pair_id');

        // 查询用户历史委托
        $OrderService = new OrderService();
        $orders = $OrderService->findHistoryOrders($userId, $pairId);

        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'order_id' => $order->order_id,
                'pair_id' => $order->pair_id,
                'type' => $order->type,
                'side' => $order->side,
                'price' => format_number($order->price),
                'amount' => $order->amount,
                'filled_amount' => $order->filled_amount,
                'status' => $order->status,
                'created_at' => $order->created_at
            ];
        }

        // 返回结果
        $this->success($result);

    }

    // 获取用户成交记录列表
    public function getFilledOrderList($f3)
    {
        // 获取用户ID
        $userId = get_current_uid();
        $pairId = $f3->get('GET.pair_id');


        // 查询用户成交记录
        $OrderService = new OrderService();
        $orders = $OrderService->findFilledOrders($userId, $pairId);
        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'order_id' => $order['order_id'],
                'pair_id' => $order['pair_id'],
                'type' => $order['type'],
                'side' => $order['side'],
                'price' => format_number($order['price']),
                'amount' => $order['amount'],
                'filled_amount' => $order['filled_amount'],
                'status' => TradeConstants::STATUS_FILLED,
                'created_at' => $order['created_at'],
                'updated_at' => $order['updated_at']
            ];
        }

        // 返回结果
        $this->success($result);
    }

    public function getLatestTrades($f3)
    {
        // 获取币种（交易对）参数，例如 BTC_USDT
        $pairId = $f3->get('GET.pair_id');

        $tradeService = new OrderService();
        $trades = $tradeService->getRecentTradesByPair($pairId, 5);

        $result = [];
        foreach ($trades as $trade) {
            $result[] = [
                'trade_id' => $trade['trade_id'],
                'price' => format_number($trade['price']),
                'amount' => format_number($trade['amount']),
                'fee' => $trade['fee'],
                'created_at' => $trade['created_at'],
            ];
        }

        $this->success(['list' => $result]);
    }

}


