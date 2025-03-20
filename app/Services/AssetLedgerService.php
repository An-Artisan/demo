<?php

namespace app\Services;

use App\Models\AssetLedgerModel;

class AssetLedgerService
{
    protected $AssetLedgerModel;

    public function __construct() {
        $this->AssetLedgerModel = new AssetLedgerModel();
    }
}