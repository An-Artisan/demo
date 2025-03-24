<?php

namespace app\Services;

use app\Constants\TradeConstants;
use app\Exceptions\AppException;
use app\Models\AssetLedgerModel;
use app\Models\UserModel;
use PDOException;
use function GuzzleHttp\Psr7\str;

class UserService
{
    protected $userModel;
    protected $assetModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->assetModel = new AssetLedgerModel();
    }

    /**
     * @throws AppException
     */
    public function createUser($data)
    {
        try {
            return $this->userModel->createUser($data);
        } catch (AppException $e) {
            throw $e;
        } catch (PDOException|\Exception $e) {
            throw new AppException($e->getMessage());
        }
    }

    public function getUsers(): array
    {
        return $this->userModel->getUsers();
    }

    public function getUserById($id)
    {
        return $this->userModel->getUserById($id);
    }

    public function updateUser($id, $data)
    {
        return $this->userModel->updateUser($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userModel->deleteUser($id);
    }

    public function getUserBalance($userId)
    {
        return $this->userModel->findByUserId($userId);
    }


    /**
     * 获取用户现货余额
     * @param $user
     * @return array
     * @author artisan
     * @email  g1090045743@gmail.com
     * @since  2025年03月23日18:52
     */
    public function getUserSpotBalances($user): array
    {
        $balance = $user['balance'];
        $result = [];
        if (isset($balance['spot'])) {
            foreach ($balance['spot'] as $entry) {
                $result[strtolower($entry['currency'])] = $entry['available'];
            }
        }
        return $result;
    }

    public function checkUserBalanceForOrder($pairId, $type, $side, $price, $amount, $balances): array
    {
        $pair = explode('_', $pairId);
        $base = strtolower($pair[0]); // 如 BTC
        $quote = strtolower($pair[1]); // 如 USDT
        $quoteBalance = $balances[$quote] ?? '0';
        $baseBalance = $balances[$base] ?? '0';

        $marketBuyCost = [
            'locked_balance' => '0',
            'cost' => '0',
            'type' => '',
            'currency' => '',
        ];

        // 限价单
        if ($type == TradeConstants::TYPE_LIMIT) {
            if ($side == TradeConstants::SIDE_BUY) {
                $totalCost = bcmul($amount, $price, 8);
                if (bccomp($quoteBalance, $totalCost, 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient balance for limit buy'];
                }
                $marketBuyCost = [
                    'locked_balance' => $totalCost,
                    'currency' => $quote
                ];
            } else {
                if (bccomp($baseBalance, $amount, 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient amount to sell'];
                }
                $marketBuyCost = [
                    'locked_balance' => $amount,
                    'currency' => $base
                ];
            }
        }

        // 市价单
        if ($type == TradeConstants::TYPE_MARKET) {
            /**
             * Binance    市价单若无深度，拒绝下单
             * OKX / Gate    市价单允许提交，但进入“排队”状态，后台维护一个挂单队列，等待对手方挂出深度再成交
             * 火币    部分场景下，转为限价单（限价价格为滑点保护价）
             * 这里如果无深度就拒绝下单，但是深度不够也可以下单，可以拆单。
             */
            if ($side == TradeConstants::SIDE_BUY) {
                $marketBuyCost = OrderService::calculateMarketBuyCost($amount, TradeConstants::RATE_LIST[$base],TradeConstants::MARKET_ORDER_BUFFER_RATE);
                if (!$marketBuyCost['success']) {
                    return ['success' => false, 'message' => $marketBuyCost['message'] ?? 'Error calculating cost'];
                }

                if (bccomp($quoteBalance, $marketBuyCost['locked_balance'], 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient balance for market buy'];
                }
                $marketBuyCost['currency'] = $quote;
            } else {
                if (bccomp($quoteBalance, $amount, 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient balance for market sell'];
                }
                $marketBuyCost['locked_balance'] = $amount;
                $marketBuyCost['currency'] = $base;
            }
        }

        return ['success' => true, 'marketBuyCost' => $marketBuyCost];
    }

    public function getAssetList(int $userId): array
    {
        return $this->assetModel->findByPairIdAndUserId($userId);
    }
}
