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
        $currency = $f3->get('GET.currency');
        $client = new GateClient();
        $data         = $client->getBalance();
        $spotData = $client->getSpotBalances(['currency' => $currency]);
        $data['spot'] = $spotData;
        $this->success($data);
    }

    public function getBalanceLocal($f3)
    {
        $currency = $f3->get('GET.currency');

        // 获取当前用户 ID
        $userId = get_current_uid();
        // 调用 Service 获取 balance 数据
        $UserBalanceService = new \app\Services\UserService();
        $balanceData = $UserBalanceService->getUserBalance($userId);

        // 解析 balance 字段（JSON）
        $balance = json_decode($balanceData['balance'], true);

        // 过滤 spot 币种（如果传入 currency）
        if ($currency && isset($balance['spot']) && is_array($balance['spot'])) {
            $balance['spot'] = array_values(array_filter($balance['spot'], function ($item) use ($currency) {
                return $item['currency'] === $currency;
            }));
        }
        // 返回结果
        $this->success($balance);
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
