<?php

namespace app\Services;

use app\Exceptions\AppException;
use app\Models\UserModel;
use PDOException;

class UserService {
    protected $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    /**
     * @throws AppException
     */
    public function createUser($data) {
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
