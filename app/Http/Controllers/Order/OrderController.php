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
        // 获取用户输入
        $userId = get_current_uid();
//        $userId = 3;
        $pairId = $f3->get('POST.pair_id');
        $type   = $f3->get('POST.type', TradeConstants::TYPE_LIMIT); // 使用常量 TradeConstants::TYPE_LIMIT 或 TradeConstants::TYPE_MARKET
        $side   = $f3->get('POST.side', TradeConstants::SIDE_BUY); // 使用常量 TradeConstants::SIDE_BUY 或 TradeConstants::SIDE_SELL
        $price  = $f3->get('POST.price'); // 限价单需要价格
        $amount = $f3->get('POST.amount');// 委托数量
        if (!$userId){
            $this->error(400, 'User not logged in');
            return;
        }
        // 验证输入
        if (!$pairId) {
            $this->error(400, 'Invalid input pairId');
            return;
        }

        $TradingPairService = new TradingPairService();
        $tradingPair      = $TradingPairService->findById($pairId);
        if (!$tradingPair) {
            $this->error(400, 'Invalid trading pair');
            return;
        }
        if ($type == TradeConstants::TYPE_LIMIT && $price <= 0) {
            $this->error(400, 'Invalid price');
            return;
        }
        if ($amount <= 0) {
            $this->error(400, 'Invalid amount');
            return;
        }
        // 创建并检查用户是否有足够的资产
        $userModel = new UserModel();
        $user      = $userModel->findById($userId);
        if (!$user) {
            $this->error(400, 'Invalid user');
            return;
        }

        $balance = get_object_vars(json_decode($user['balance']));
        $balance = array_change_key_case($balance, CASE_LOWER);
        //拆解交易对
        $tradingPair = explode('_', $pairId);
        $tradingPair = array_map('strtolower', $tradingPair);

        $currency_base_balance = $balance[$tradingPair[0]];//交易对的基础币余额
        $currency_quote_balance = $balance[$tradingPair[1]];//交易对的计价币余额

        //检查余额是否足够 限价单
        if ($type == TradeConstants::TYPE_LIMIT) {

            //限价买入 计价币余额必须大于等于 委托数量 * 价格
            if ($side == TradeConstants::SIDE_BUY && $currency_quote_balance < $amount * $price) {
                $this->error(400, 'Insufficient balance');
                return;
            }
            //限价卖出 基础币余额必须大于等于 委托数量
            if ($side == TradeConstants::SIDE_SELL && $currency_base_balance < $amount) {
                $this->error(400, 'Insufficient amount to sell');
                return;
            }
        } else {
            //检查余额是否足够 市价单
            //市价买入 用户计价币余额小于等于0返回异常
            if ($side == TradeConstants::SIDE_BUY && $currency_quote_balance <= 0) {
                $this->error(400, 'Insufficient balance');
                return;
            }
            //市价卖出 用户基础币余额需小于委托数量
            if ($side == TradeConstants::SIDE_SELL && $currency_base_balance < $amount ) {
                $this->error(400, 'Insufficient amount to sell');
                return;
            }
        }
        //创建订单
        $order = [
            'user_id'    => $userId,
            'pair_id'    => $pairId,
            'type'       => $type,
            'side'       => $side,
            'price'      => $price,
            'amount'     => $amount
        ];

        $OrderService = new OrderService();
        $order_id     = $OrderService->createOrder($order);

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
//        $userId = 1;
        // 获取分页参数
        $page   = $f3->get('GET.page') ? (int)$f3->get('GET.page') : 1;
        $limit  = $f3->get('GET.limit') ? (int)$f3->get('GET.limit') : 10;
        $offset = ($page - 1) * $limit;

        // 查询用户当前委托（未完全成交的订单）
        $OrderService = new OrderService();
        $orders     = $OrderService->findCurrentOrders($userId, $limit, $offset);

        // 查询总记录数
        $total = $OrderService->countCurrentOrders($userId);

        // 格式化返回数据
        $result = [
            'list'       => [],
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
        foreach ($orders as $order) {
            $result['list'][] = [
                'order_id'      => $order->order_id,
                'pair_id'       => $order->pair_id,
                'type'          => $order->type,
                'side'          => $order->side,
                'price'         => $order->price,
                'amount'        => $order->amount,
                'filled_amount' => $order->filled_amount,
                'status'        => $order->status,
                'created_at'    => $order->created_at
            ];
        }

        // 返回结果
        $this->success($result);
    }

    // 获取用户历史委托列表
    public function getHistoryOrderList($f3)
    {
        // 获取用户ID
        $userId = get_current_uid();
//        $userId = 1;

        // 获取分页参数
        $page   = $f3->get('GET.page') ? (int)$f3->get('GET.page') : 1;
        $limit  = $f3->get('GET.limit') ? (int)$f3->get('GET.limit') : 10;
        $offset = ($page - 1) * $limit;

        // 获取排序参数（默认按创建时间倒序）
        $sortField = $f3->get('GET.sort_field') ?: 'created_at';
        $sortOrder = $f3->get('GET.sort_order') ?: 'DESC';

        // 查询用户历史委托
        $OrderService = new OrderService();
        $orders     = $OrderService->findHistoryOrders($userId, $limit, $offset, $sortField, $sortOrder);

        // 查询总记录数
        $total = $OrderService->countHistoryOrders($userId);

        // 格式化返回数据
        $result = [
            'list'       => [],
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
        foreach ($orders as $order) {
            $result['list'][] = [
                'order_id'      => $order->order_id,
                'pair_id'       => $order->pair_id,
                'type'          => $order->type,
                'side'          => $order->side,
                'price'         => $order->price,
                'amount'        => $order->amount,
                'filled_amount' => $order->filled_amount,
                'status'        => $order->status,
                'created_at'    => $order->created_at
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

        // 获取分页参数
        $page   = $f3->get('GET.page') ? (int)$f3->get('GET.page') : 1;
        $limit  = $f3->get('GET.limit') ? (int)$f3->get('GET.limit') : 10;
        $offset = ($page - 1) * $limit;

        // 获取排序参数（默认按成交时间倒序）
        $sortField = $f3->get('GET.sort_field') ?: 'updated_at';
        $sortOrder = $f3->get('GET.sort_order') ?: 'DESC';

        // 查询用户成交记录
        $orderModel = new OrderModel();
        $orders     = $orderModel->findFilledOrders($userId, $limit, $offset, $sortField, $sortOrder);

        // 查询总记录数
        $total = $orderModel->countFilledOrders($userId);

        // 格式化返回数据
        $result = [
            'data'       => [],
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
        foreach ($orders as $order) {
            $result['data'][] = [
                'order_id'      => $order->order_id,
                'pair_id'       => $order->pair_id,
                'type'          => $order->type,
                'side'          => $order->side,
                'price'         => $order->price,
                'amount'        => $order->amount,
                'filled_amount' => $order->filled_amount,
                'status'        => $order->status,
                'created_at'    => $order->created_at,
                'updated_at'    => $order->updated_at
            ];
        }

        // 返回结果
        $this->success($result);
    }

}


