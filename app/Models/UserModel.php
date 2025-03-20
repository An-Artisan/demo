<?php

namespace app\Models;

use DB\SQL\Mapper;
use lib\encryption\Encryption;

class UserModel extends \DB\SQL\Mapper {
    public function __construct($db) {
        parent::__construct($db, 'users');
    }

    // 创建用户
    public function createUser($data) {
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
}
