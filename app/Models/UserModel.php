<?php

namespace app\Models;

use app\Constants\TradeConstants;
use app\Exceptions\AppException;
use Base;
use DB\SQL\Mapper;
use lib\encryption\Encryption;

class UserModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(db(), 'users');
    }

    // 创建用户

    /**
     * @throws AppException
     */
    public function createUser($data) {

        if (empty($data['username'])) {
            // 抛出自定义的应用错误
            throw new AppException('用户名不能为空', 10001);
        }

        // 确保 balance 是 JSON 格式
        if (empty($data['balance'])) {
            $data['balance'] = json_encode([], JSON_UNESCAPED_UNICODE);
        } elseif (is_array($data['balance'])) {
            $data['balance'] = json_encode($data['balance'], JSON_UNESCAPED_UNICODE);
        }

        // 加密密码
        if (!empty($data['password'])) {
            $data['password_hash'] = Encryption::hashPassword($data['password']);
            unset($data['password']); // 确保不存储明文密码
        }

        $this->copyFrom($data);
        $user = $this->save();
        return $user->user_id;
    }

    // 获取所有用户
    public function getUsers(): array
    {
        // 获取所有用户数据
        $users = $this->find();

        $userData = [];  // 存放用户数据的数组

        foreach ($users as $user) {
            $userData[] = [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'email' => $user->email,
                'balance' => json_decode($user->balance, true),  // 解析 JSON 格式的 balance 字段
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];
        }

        return $userData;
    }

    public function getUserById($id): ?array
    {
        // 获取用户数据，返回对象
        $user = $this->load(['user_id=?', $id]);

        if ($user) {
            // 将对象转换为数组返回
            return [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'email' => $user->email,
                'balance' => json_decode($user->balance, true),  // 解析 JSON 数据
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];
        } else {
            return null; // 如果没有找到用户，返回 null
        }
    }
    // 更新用户
    public function updateUser($id, $data): bool
    {
        $this->load(['user_id=?', $id]);
        if (empty($data['balance'])) {
            $data['balance'] = json_encode([], JSON_UNESCAPED_UNICODE);
        } elseif (is_array($data['balance'])) {
            $data['balance'] = json_encode($data['balance'], JSON_UNESCAPED_UNICODE);
        }
        if (!$this->dry()) {
            $this->copyfrom($data);
            $this->update();
            return true;
        }
        return false;
    }

    // 删除用户
    public function deleteUser($id): bool
    {
        $this->load(['user_id=?', $id]);
        if (!$this->dry()) {
            $this->erase();
            return true;
        }
        return false;
    }

    // 根据用户ID查找用户
    public function findById($userId) {
        $this->load(['user_id = ?', $userId]);
        return $this->cast();
    }

// 根据用户名查找用户
    public function findByUsername($username) {
        $this->load(['username = ?', $username]);
        return $this->query;
    }

// 更新用户资产余额
    public function updateBalance($userId, $balance) {
        $this->load(['user_id = ?', $userId]);
        $this->balance = json_encode($balance);
        $this->save();
    }

    public function findByUserId($userId)
    {
        $this->load(['user_id = ?', $userId]);
        return $this->dry() ? [] : $this->cast();
    }

    /**
     * 余额变更
     * @param $userId
     * @param $currency
     * @param $amount
     * @throws \Exception
     * @author liuqiang
     * @email  liuqiang@smzdm.com
     * @since  2025年03月23日19:52
     */
    public function deductBalance($userId, $currency, $amount)
    {
        $this->load(['user_id = ?', $userId]);
        $balance = json_decode($this->balance, true);

        if (!isset($balance['spot'])) $balance['spot'] = [];

        $found = false;
        foreach ($balance['spot'] as &$asset) {
            if (strtolower($asset['currency']) === strtolower($currency)) {
                if (bccomp($asset['available'], $amount, 8) < 0) {
                    throw new \Exception("余额不足，无法锁定");
                }
                $asset['available'] = bcsub($asset['available'], $amount, 8);
                $asset['locked'] = bcadd($asset['locked'], $amount, 8);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new \Exception("用户资产中未找到币种：" . $currency);
        }

        // 更新折算总额，包含 finance 和 futures
        $totalUSDT = '0';
        $rates = [
            'btc' => TradeConstants::RATE_BTC,
            'eth' => TradeConstants::RATE_ETH
        ];
        foreach ($balance['spot'] as $item) {
            $cur = strtolower($item['currency']);
            $available = $item['available'] ?? '0';
            if ($cur === 'usdt') {
                $totalUSDT = bcadd($totalUSDT, $available, 8);
            } elseif (isset($rates[$cur])) {
                $totalUSDT = bcadd($totalUSDT, bcmul($available, $rates[$cur], 8), 8);
            }
        }

        $financeUSDT = $balance['details']['finance']['amount'] ?? '0';
        $futuresUSDT = $balance['details']['futures']['amount'] ?? '0';
        $finalTotal = bcadd(bcadd($totalUSDT, $financeUSDT, 8), $futuresUSDT, 8);

        $balance['details']['spot']['amount'] = $totalUSDT;
        $balance['total']['amount'] = $finalTotal;
        $this->balance = json_encode($balance, JSON_UNESCAPED_UNICODE);
        $this->save();
    }
}
