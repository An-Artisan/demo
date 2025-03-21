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

    public function index($f3)
    {

//        $apiKey = Load::get('gate.api_key');
//        $apiSecret = Load::get('gate.api_secret');
//        $client = new GateClient($apiKey, $apiSecret);
//
//        // ✅ 获取账户余额（不包含详细信息）
//        $balance = $client->getBalance();
//        dd($balance);
        $users = $this->userService->getUsers();
        $this->success($users);
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
