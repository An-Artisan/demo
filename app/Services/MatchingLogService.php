<?php

namespace app\Services;

use App\Models\MatchingLogModel;

class MatchingLogService
{
    protected $MatchingLogModel;

    public function __construct() {
        $this->MatchingLogModel = new MatchingLogModel();
    }
}