<?php

namespace Biz\Marketing\Dao\Impl;

use Biz\Marketing\Dao\UserMarketingActivitySynclogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserMarketingActivitySynclogDaoImpl extends GeneralDaoImpl implements UserMarketingActivitySynclogDao
{
    protected $table = 'user_marketing_activity_sync_log';

    public function getLastSyncLogByTargetAndTargetValue($target, $targetValue)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE `target` = ? AND `targetValue` = ? ORDER BY id DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($target, $targetValue)) ?: null;
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'args' => 'json',
                'data' => 'json',
            ),
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(),
        );
    }
}
