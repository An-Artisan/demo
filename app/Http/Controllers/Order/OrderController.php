<?php
namespace App\Http\Controllers\Order;

use app\Http\Traits\JsonResponseTrait;
use lib\config\Load;
use App\Models\TradeModel;
use App\Models\OrderModel;
use App\Models\UserModel;
use App\Models\TradingPairModel;
use App\Models\MatchingEngineModel;
use lib\TradeConstants;
use lib\Base;
class OrderController {
    use JsonResponseTrait;
    // 用户提交订单
    public function placeOrder($f3) {
        $db = config("database");
        $orderModel = new OrderModel();
        $matchingEngineModel = new MatchingEngineModel();

        try {
            // 开启事务
            $db->begin();

            // 获取用户输入
            $userId = $f3->get('POST.user_id');
            $pairId = $f3->get('POST.pair_id');
            $type = $f3->get('POST.type'); // 使用常量 TradeConstants::TYPE_LIMIT 或 TradeConstants::TYPE_MARKET
            $side = $f3->get('POST.side'); // 使用常量 TradeConstants::SIDE_BUY 或 TradeConstants::SIDE_SELL
            $price = $f3->get('POST.price'); // 限价单需要价格
            $amount = $f3->get('POST.amount');

            // 验证用户和交易对
            $userModel = new UserModel();
            $tradingPairModel = new TradingPairModel();
            if (!$userModel->findById($userId) || !$tradingPairModel->findById($pairId)) {
                $f3->error(400, 'Invalid user or trading pair');
                return;
            }

            // 创建订单
            $orderId = $orderModel->createOrder($userId, $pairId, $type, $side, $price, $amount);

            // 触发撮合引擎
            $matchingEngineModel->matchOrders($pairId);

            // 提交事务
            $db->commit();

            // 返回订单ID
            $this->success(['order_id' => $orderId]);

        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            $this->error(500,'Failed to place order: ' . $e->getMessage());
        }
    }
}