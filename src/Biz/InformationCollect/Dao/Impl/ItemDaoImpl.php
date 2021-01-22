<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ItemDaoImpl extends AdvancedDaoImpl implements ItemDao
{
    protected $table = 'information_collect_item';

    public function findByEventId($eventId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE eventId = ? ORDER BY seq ASC, id ASC;";

        return $this->db()->fetchAll($sql, [$eventId]) ?: [];
    }

    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id', 'seq',
            ],
            'timestamps' => [
                'createdTime',
            ],
            'conditions' => [
                'id = :id',
                'eventId = :eventId',
            ],
        ];
    }
}
