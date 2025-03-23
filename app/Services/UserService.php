<?php

namespace app\Services;

use app\Constants\TradeConstants;
use app\Exceptions\AppException;
use app\Models\UserModel;
use PDOException;
use function GuzzleHttp\Psr7\str;

class UserService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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
                    'cost' => $totalCost,
                    'type' => 'limit_buy',
                    'currency' => $quote
                ];
            } else {
                if (bccomp($baseBalance, $amount, 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient amount to sell'];
                }
                $marketBuyCost = [
                    'locked_balance' => $amount,
                    'cost' => $amount,
                    'type' => 'limit_sell',
                    'currency' => $base
                ];
            }
        }

        // 市价单
        if ($type == TradeConstants::TYPE_MARKET) {
            if ($side == TradeConstants::SIDE_BUY) {
                $marketBuyCost = OrderService::calculateMarketBuyCost($pairId, $amount, TradeConstants::MARKET_ORDER_BUFFER_RATE);
                if (!$marketBuyCost['success']) {
                    return ['success' => false, 'message' => $marketBuyCost['message'] ?? 'Error calculating cost'];
                }
                if (bccomp($quoteBalance, $marketBuyCost['locked_balance'], 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient balance for market buy'];
                }
                $marketBuyCost['type'] = 'market_buy';
                $marketBuyCost['currency'] = $quote;
            } else {
                if (bccomp($baseBalance, $amount, 8) < 0) {
                    return ['success' => false, 'message' => 'Insufficient amount to sell'];
                }
                $marketBuyCost = [
                    'locked_balance' => $amount,
                    'cost' => $amount,
                    'type' => 'market_sell',
                    'currency' => $base
                ];
            }
        }

        return ['success' => true, 'marketBuyCost' => $marketBuyCost];
    }

}
