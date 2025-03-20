<?php

namespace app\Models;

use DB\SQL\Mapper;
use lib\encryption\Encryption;

class UserModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(Base::instance()->get('DB'), 'users');
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
    public function getUsers() {
        return $this->find();
    }

    // 获取单个用户
    public function getUserById($id) {
        return $this->load(['id=?', $id]);
    }

    // 更新用户
    public function updateUser($id, $data) {
        $this->load(['id=?', $id]);
        if (!$this->dry()) {
            $this->copyfrom($data);
            $this->update();
            return true;
        }
        return false;
    }

    // 删除用户
    public function deleteUser($id) {
        $this->load(['id=?', $id]);
        if (!$this->dry()) {
            $this->erase();
            return true;
        }
        return false;
    }

    // 根据用户ID查找用户
    public function findById($userId) {
        $this->load(['user_id = ?', $userId]);
        return $this->query;
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
}
