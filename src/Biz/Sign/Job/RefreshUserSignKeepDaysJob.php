<?php

namespace Biz\Sign\Job;

use Biz\Sign\Dao\SignUserStatisticsDao;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshUserSignKeepDaysJob extends AbstractJob
{
    const LIMIT = 100000;

    public function execute()
    {
        $conditions = ['lastSignTime_LT' => strtotime(date('Y-m-d', strtotime('-1 day')).' 00:00:00')];
        $total = $this->getSignUserStatisticsDao()->count($conditions);

        for ($i = 0; $i < ($total / self::LIMIT); ++$i) {
            $updateStatistics = $this->getSignUserStatisticsDao()->search($conditions, ['id' => 'ASC'], $i * self::LIMIT, self::LIMIT);
            if (empty($updateStatistics)) {
                continue;
            }
            $this->getSignUserStatisticsDao()->update(['ids' => array_column($updateStatistics, 'id')], ['keepDays' => 0]);
        }
    }

    /**
     * @return SignUserStatisticsDao
     */
    protected function getSignUserStatisticsDao()
    {
        return $this->biz->dao('Sign:SignUserStatisticsDao');
    }
}
