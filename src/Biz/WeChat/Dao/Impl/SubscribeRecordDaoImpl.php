<?php

namespace Biz\WeChat\Dao\Impl;

use Biz\WeChat\Dao\SubscribeRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class SubscribeRecordDaoImpl extends AdvancedDaoImpl implements SubscribeRecordDao
{
    protected $table = 'wechat_subscribe_record';

    public function getLastRecord()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql) ?: null;
    }

    public function declares()
    {
        return [
            'orderbys' => ['id'],
            'conditions' => [
                'id = :id',
                'toId = :toId',
                'toId IN (:toIds)',
                'templateCode = :templateCode',
                'templateType = :templateType',
                'isSend < :isSend_LT',
                'id IN (:ids)',
            ],
            'timestamps' => ['createdTime', 'updatedTime'],
        ];
    }
}
