<?php

namespace app\Models;

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
}
