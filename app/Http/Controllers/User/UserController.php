<?php

namespace app\Http\Controllers\User;

use app\Http\Traits\JsonResponseTrait;
use app\Services\UserService;
use Base;

class UserController {
    protected $userService;
    use JsonResponseTrait;
    public function __construct() {
        $db = Base::instance()->get('DB');
        $this->userService = new UserService($db);
    }

    public function index($f3) {
        $users = $this->userService->getUsers();
        echo json_encode(['code' => 200, 'data' => $users], JSON_UNESCAPED_UNICODE);
    }

    public function show($f3, $params) {
        $user = $this->userService->getUserById($params['id']);
        if ($user) {
            echo json_encode(['code' => 200, 'data' => $user], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['code' => 404, 'message' => '用户不存在'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function store($f3) {
        $data = json_decode($f3->get('BODY'), true);
        $userId = $this->userService->createUser($data);
        $this->success(['user_id' => $userId]);
    }

    public function update($f3, $params) {
        $data = json_decode($f3->get('BODY'), true);
        $updated = $this->userService->updateUser($params['id'], $data);
        if ($updated) {
            echo json_encode(['code' => 200, 'message' => '用户更新成功'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['code' => 404, 'message' => '用户不存在'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy($f3, $params) {
        $deleted = $this->userService->deleteUser($params['id']);
        if ($deleted) {
            echo json_encode(['code' => 200, 'message' => '用户删除成功'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['code' => 404, 'message' => '用户不存在'], JSON_UNESCAPED_UNICODE);
        }
    }
}
