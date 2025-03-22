<?php

namespace app\Http\Controllers\Order;

use app\Http\Controllers\BaseController;
use app\Models\OrderModel;
use app\Models\TradingPairModel;
use app\Models\UserModel;
use app\Services\MatchingEngineService;
use Base;
use app\Constants;
use app\Services\OrderService;

class OrderController extends BaseController
{
    // 用户提交订单
    public function createOrder($f3)
    {
        // 获取用户输入
        $userId = get_current_user_id();
        $pairId = $f3->get('POST.pair_id');
        $type   = $f3->get('POST.type'); // 使用常量 TradeConstants::TYPE_LIMIT 或 TradeConstants::TYPE_MARKET
        $side   = $f3->get('POST.side'); // 使用常量 TradeConstants::SIDE_BUY 或 TradeConstants::SIDE_SELL
        $price  = $f3->get('POST.price'); // 限价单需要价格
        $amount = $f3->get('POST.amount');// 委托数量
        if (!$userId){
            $this->error(400, 'User not logged in');
            return;
        }
        // 验证输入
        if (!$pairId || !$type || !$side || !$price || !$amount) {
            $this->error(400, 'Invalid input');
            return;
        }
        $tradingPairModel = new TradingPairModel();
        $tradingPair      = $tradingPairModel->findById($pairId);
        if (!$tradingPair) {
            $this->error(400, 'Invalid trading pair');
            return;
        }
        if ($type == Constants::TYPE_LIMIT && $price <= 0) {
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

        if ($type == Constants::TYPE_LIMIT) {
            if ($side == Constants::SIDE_BUY && $user->available_balance < $amount * $price) {
                $this->error(400, 'Insufficient balance');
                return;
            }
            if ($side == Constants::SIDE_SELL && $user->available_balance < $amount) {
                $this->error(400, 'Insufficient balance');
                return;
            }
        } else {
            if ($side == Constants::SIDE_BUY && $user->available_balance < $amount) {
                $this->error(400, 'Insufficient balance');
                return;
            }
            if ($side == Constants::SIDE_SELL && $user->available_balance < $amount * $tradingPair->price) {
                $this->error(400, 'Insufficient balance');
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
    public function getCurrentOrders($f3)
    {
        // 获取用户ID
        $userId = get_current_user_id();

        // 获取分页参数
        $page   = $f3->get('GET.page') ? (int)$f3->get('GET.page') : 1;
        $limit  = $f3->get('GET.limit') ? (int)$f3->get('GET.limit') : 10;
        $offset = ($page - 1) * $limit;

        // 查询用户当前委托（未完全成交的订单）
        $orderModel = new OrderModel();
        $orders     = $orderModel->findCurrentOrders($userId, $limit, $offset);

        // 查询总记录数
        $total = $orderModel->countCurrentOrders($userId);

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
                'created_at'    => $order->created_at
            ];
        }

        // 返回结果
        $this->success($result);
    }

    // 获取用户历史委托列表
    public function getHistoryOrders($f3)
    {
        // 获取用户ID
        $userId = get_current_user_id();

        // 获取分页参数
        $page   = $f3->get('GET.page') ? (int)$f3->get('GET.page') : 1;
        $limit  = $f3->get('GET.limit') ? (int)$f3->get('GET.limit') : 10;
        $offset = ($page - 1) * $limit;

        // 获取排序参数（默认按创建时间倒序）
        $sortField = $f3->get('GET.sort_field') ?: 'created_at';
        $sortOrder = $f3->get('GET.sort_order') ?: 'DESC';

        // 查询用户历史委托
        $orderModel = new OrderModel();
        $orders     = $orderModel->findHistoryOrders($userId, $limit, $offset, $sortField, $sortOrder);

        // 查询总记录数
        $total = $orderModel->countHistoryOrders($userId);

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
                'created_at'    => $order->created_at
            ];
        }

        // 返回结果
        $this->success($result);

    }

    // 获取用户成交记录列表
    public function getFilledOrders($f3)
    {
        // 获取用户ID
        $userId = get_current_user_id();

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


