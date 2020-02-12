<?php

namespace Biz\DestroyAccount\Dao\Impl;

use Biz\DestroyAccount\Dao\DestroyAccountRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DestroyAccountRecordDaoImpl extends GeneralDaoImpl implements DestroyAccountRecordDao
{
    protected $table = 'destroy_account_record';

    public function getLastDestroyAccountRecordByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} where $userId = ? ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime', 'passedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'userId = :userId',
                'nickname like :nickname',
            ),
        );
    }
}
