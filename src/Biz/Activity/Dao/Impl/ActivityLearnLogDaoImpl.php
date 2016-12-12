<?php

namespace Biz\Activity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Activity\Dao\ActivityLearnLogDao;

class ActivityLearnLogDaoImpl extends GeneralDaoImpl implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    public function sumLearnTimeByActivityIdAndUserId($activityId, $userId)
    {
    	$sql = "SELECT sum(learnTime) FROM {$this->table()} WHERE activityId = ? and userId = ? ";
        return $this->db()->fetchColumn($sql, array($activityId, $userId)) ?: 0;
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json'
            )
        );
    }


}
