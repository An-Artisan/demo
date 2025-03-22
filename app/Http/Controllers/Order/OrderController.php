<?php

namespace app\Http\Controllers\Order;

use app\Http\Controllers\BaseController;
use app\Models\OrderModel;
use app\Models\TradingPairModel;
use app\Models\UserModel;
use app\Services\MatchingEngineService;
use app\Services\TradingPairService;
use Base;
use app\Constants\TradeConstants;
use app\Services\OrderService;
use lib\gate\GateClient;

class OrderController extends BaseController
{
    // 用户提交订单
    public function createOrder($f3)
    {
        $userId = get_current_uid();
        $body = json_decode($f3->get('BODY'), true);
        $pairId = $body['pair_id'] ?? 'BTC_USDT';
        $type = $body['type'] ?? 0;
        $side = $body['side'] ?? 0;
        $price = $body['price'] ?? 0;
        $amount = $body['amount'] ?? 0;

        if (!$userId) {
            $this->error(400, 'User not logged in');
            return;
        }

        if (!$pairId) {
            $this->error(400, 'Invalid input pairId');
            return;
        }

        $TradingPairService = new TradingPairService();
        $tradingPair = $TradingPairService->findById($pairId);
        if (!$tradingPair) {
            $this->error(400, 'Invalid trading pair');
            return;
        }

        if ($type == TradeConstants::TYPE_LIMIT && bccomp($price, 0, 8) <= 0) {
            $this->error(400, 'Invalid price');
            return;
        }

        if (bccomp($amount, 0, 8) <= 0) {
            $this->error(400, 'Invalid amount');
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findById($userId);
        if (!$user) {
            $this->error(400, 'Invalid user');
            return;
        }

        $tradingPair = explode('_', $pairId);
        $tradingPair = array_map('strtolower', $tradingPair);
        $currency_base_balance = 0;
        $currency_quote_balance = 0;

        $balance = get_object_vars(json_decode($user['balance']));
        if ($balance['spot']) {
            foreach ($balance['spot'] as $value) {
                $tmp_balance = get_object_vars($value);
                if (strtolower($tmp_balance['currency']) == $tradingPair[0]) {
                    $currency_base_balance = $tmp_balance['available'];
                }
                if (strtolower($tmp_balance['currency']) == $tradingPair[1]) {
                    $currency_quote_balance = $tmp_balance['available'];
                }
            }
        }

        if ($type == TradeConstants::TYPE_LIMIT) {
            if ($side == TradeConstants::SIDE_BUY && bccomp($currency_quote_balance, bcmul($amount, $price, 8), 8) < 0) {
                $this->error(400, 'Insufficient balance');
                return;
            }
            if ($side == TradeConstants::SIDE_SELL && bccomp($currency_base_balance, $amount, 8) < 0) {
                $this->error(400, 'Insufficient amount to sell');
                return;
            }
        } else {
            if ($side == TradeConstants::SIDE_BUY && bccomp($currency_quote_balance, 0, 8) <= 0) {
                $this->error(400, 'Insufficient balance');
                return;
            }
            if ($side == TradeConstants::SIDE_SELL && bccomp($currency_base_balance, $amount, 8) < 0) {
                $this->error(400, 'Insufficient amount to sell');
                return;
            }
        }

        $order = [
            'user_id' => $userId,
            'pair_id' => $pairId,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount
        ];

        $OrderService = new OrderService();
        $order_id = $OrderService->createOrder($order);

        if ($order_id) {
            $this->success(['order_id' => $order_id], 'Order placed successfully');
        } else {
            $this->error(500, 'Failed to place order');
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
                'price' => $order->price,
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

            if ($amount <= 0) continue;

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
            $askArr[] = [(string)$price, (string)$amount];
        }
        foreach ($bids as $price => $amount) {
            $bidArr[] = [(string)$price, (string)$amount];
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
                'price' => $order->price,
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
                'order_id'      => $order['order_id'],
                'pair_id'       => $order['pair_id'],
                'type'          => $order['type'],
                'side'          => $order['side'],
                'price'         => $order['price'],
                'amount'        => $order['amount'],
                'filled_amount' => $order['filled_amount'],
                'status'        => TradeConstants::STATUS_FILLED,
                'created_at'    => $order['created_at'],
                'updated_at'    => $order['updated_at']
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
                'price' => $trade['price'],
                'amount' => $trade['amount'],
                'fee' => $trade['fee'],
                'created_at' => $trade['created_at'],
            ];
        }

        $this->success(['list' => $result]);
    }

}


