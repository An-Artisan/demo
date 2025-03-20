<?php

namespace app\Services;

use app\Models\UserModel;

class UserService {
    protected $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    public function createUser($data) {
        return $this->userModel->createUser($data);
    }

    public function getUsers(): array
    {
        return $this->userModel->getUsers();
    }

    public function getUserById($id) {
        return $this->userModel->getUserById($id);
    }

    public function updateUser($id, $data) {
        return $this->userModel->updateUser($id, $data);
    }

    public function deleteUser($id) {
        return $this->userModel->deleteUser($id);
    }
}
