<?php
namespace app\Models;

/**
 * 资产流水模型
 */
class AssetLedgerModel extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(db(), 'asset_ledger');
    }

    // 根据流水ID查找流水记录
    public function findById($ledgerId) {
        $this->load(['ledger_id = ?', $ledgerId]);
        return $this->query;
    }

    // 根据用户ID查找用户流水记录
    public function findByUserId($userId) {
        return $this->find(['user_id = ?', $userId]);
    }

    // 创建新流水记录
    public function createLedger($userId, $currency, $amount, $type, $relatedOrderId = null) {
        $model = new $this;
        $model->user_id = $userId;
        $model->currency = $currency;
        $model->amount = $amount;
        $model->type = $type;
        $model->related_order_id = $relatedOrderId;
        $model->save();
        return $model->ledger_id;
    }
}
