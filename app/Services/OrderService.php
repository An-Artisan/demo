<?php

namespace app\Services;

use App\Models\OrderModel;

class OrderService
{
    protected $OrderModel;

    public function __construct() {
        $this->OrderModel = new OrderModel();
    }
}