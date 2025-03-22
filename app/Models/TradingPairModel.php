<?php

namespace app\Models;

use Base;

/**
 * 交易对模型
 */
class TradingPairModel extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(Base::instance()->get('DB'), 'trading_pairs');
    }

    // 根据交易对ID查找交易对
    public function findById($pairId)
    {
        $this->load(['id = ?', $pairId]);
        return $this->query;
    }

    // 查找所有可用交易对
    public function findAllActive()
    {
        return $this->find(['trade_status = ?', 'tradable']);
    }

    // 根据交易对名称查找交易对
    public function findByName($name)
    {
        return $this->find(['base_name = ?', $name]);
    }

    //交易对数据批量入库
    public function insertBatch($pairs)
    {
        $this->insertBatch($pairs);
    }

    //更新或者插入交易对数据
    public function saveOrUpdate($pair)
    {
        $this->load(['id = ?', $pair['id']]);
        if ($this->dry()) {
            $this->copyFrom($pair);
            $this->insert();
        } else {
            $this->copyFrom($pair);
            $this->save();
        }
    }
}

