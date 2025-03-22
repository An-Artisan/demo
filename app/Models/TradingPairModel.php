<?php

namespace app\Models;

use Base;

/**
 * 交易对模型
 */
class TradingPairModel extends \DB\SQL\Mapper
{
    protected $table = 'trading_pairs';
    public function __construct()
    {
        parent::__construct(Base::instance()->get('DB'), $this->table);
    }

    // 根据交易对ID查找交易对
    public function findById($pairId)
    {
        $this->load(['id = ?', $pairId]);
        return $this->cast();
    }

    // 查找所有可用交易对
    public function findAllActive()
    {
        $result = [];
        $results = $this->find(['trade_status = ?', 'tradable']);
        foreach ($results as $res) {
            $result[] = $res->cast();
        }
        return $result;
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

