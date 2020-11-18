<?php

namespace Biz\Visualization\Service\Impl;

use Biz\BaseService;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Service\LearnControlService;

class LearnControlServiceImpl extends BaseService implements LearnControlService
{
    public function getUserLastLearnRecord($userId)
    {
        return $this->getActivityLearnRecordDao()->getUserLastLearnRecord($userId);
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->biz->dao(['Visualization:ActivityLearnRecordDao']);
    }
}
