<?php

namespace app\Http\Controllers\User;

use app\Exceptions\AppException;
use app\Http\Traits\JsonResponseTrait;
use app\Services\UserService;
use Base;
use lib\config\Load;
use lib\gate\GateClient;

class UserController
{
    protected $userService;
    use JsonResponseTrait;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function getBalance($f3)
    {
        $client = new GateClient();
        $data         = $client->getBalance();
        $this->success($data);
    }

    public function index($f3)
    {
        $data = $this->userService->getUsers();
        $this->success($data);
    }

    public function show($f3, $params)
    {
        $user = $this->userService->getUserById($params['id']);
        if ($user) {
            $this->success($user);
        } else {
            $this->error(400, '用户不存在');
        }
    }

    /**
     * @throws AppException
     */
    public function store($f3)
    {
        $data = json_decode($f3->get('BODY'), true);
        try {
            $userId = $this->userService->createUser($data);
            $this->success(['user_id' => $userId]);
        } catch (AppException $e) {
            $this->error($e->getCode(), $e->getMessage());
        }
    }

    public function update($f3, $params)
    {
        $data = json_decode($f3->get('BODY'), true);
        $updated = $this->userService->updateUser($params['id'], $data);
        if ($updated) {
            $this->success([], '用户更新成功');
        } else {
            $this->error(400, '用户不存在');
        }
    }

    public function destroy($f3, $params)
    {
        $deleted = $this->userService->deleteUser($params['id']);
        if ($deleted) {
            $this->success([], '用户删除成功');
        } else {
            $this->error(400, '用户不存在');
        }
    }
}
