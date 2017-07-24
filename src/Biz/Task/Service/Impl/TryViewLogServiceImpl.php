<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Task\Dao\TryViewLogDao;
use Biz\Task\Service\TryViewLogService;

class TryViewLogServiceImpl extends BaseService implements TryViewLogService
{
    public function createTryViewLog($tryViewLog)
    {
        return $this->getTryViewLogDao()->create($tryViewLog);
    }

    public function searchTryViewLogs($conditions, $sortBys, $start, $limit)
    {
        return $this->getTryViewLogDao()->search($conditions, $sortBys, $start, $limit);
    }

    public function countTryViewLogs($conditions)
    {
        return $this->getTryViewLogDao()->count($conditions);
    }

    public function searchLogCountsByCourseIdAndTimeRange($courseId, $timeRange = array(), $format = '%Y-%m-%d')
    {
        $conditions = array(
            'courseId' => $courseId,
        );
        if (!empty($timeRange)) {
            $conditions['startTimeGreaterThan'] = strtotime($timeRange['startDate']);
            $conditions['startTimeLessThan'] = empty($timeRange['endDate']) ? time() : strtotime($timeRange['endDate']);
        }

        return $this->getTryViewLogDao()->searchLogCountsByConditionsGroupByCreatedTimeWithFormat($conditions, $format);
    }

    /**
     * @return TryViewLogDao
     */
    protected function getTryViewLogDao()
    {
        return $this->createDao('Task:TryViewLogDao');
    }
}
