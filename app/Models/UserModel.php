<?php

namespace App\Models;

use DB\SQL\Mapper;

class UserModel extends \DB\SQL\Mapper {
    public function __construct($db) {
        parent::__construct($db, 'users');
    }

    // 创建用户
    public function createUser($data) {
        $this->copyfrom($data);
        $this->save();
        return $this->id;
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
