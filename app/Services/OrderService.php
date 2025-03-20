<?php

namespace app\Services;

use App\Models\OrderModel;

class OrderService
{
    protected $AssetLedgerModel;

    public function __construct() {
        $this->AssetLedgerModel = new AssetLedgerModel();
    }
}