<?php

namespace Mooc\Service\CrontabJob\Impl;

use Mooc\Service\CrontabJob\CrontabJobService;
use Topxia\Service\Crontab\Impl\CrontabServiceImpl as BaseServiceImpl;

class CrontabJobServiceImpl extends BaseServiceImpl implements CrontabJobService
{
    public function getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId)
    {
        return $this->getJobDao()->getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
    }

    public function updateJob($id, $fields)
    {
        $job = $this->getJobDao()->updateJob($id, $fields);
        $this->refreshNextExecutedTime();
        return $job;
    }

    public function searchJobs($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        switch ($sort) {
            case 'created':
                $sort = array('createdTime', 'DESC');
                break;
            case 'createdByAsc':
                $sort = array('createdTime', 'ASC');
                break;
            case 'nextExcutedTime':
                $sort = array('nextExcutedTime', 'DESC');
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
                break;
        }

        $jobs = $this->getJobDao()->searchJobs($conditions, $sort, $start, $limit);

        return $jobs;
    }

    public function searchJobsCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);
        return $this->getJobDao()->searchJobsCount($conditions);
    }

    public function executeJob($id)
    {
        $job = array();
        try {
            // 开始执行job的时候，设置next_executed_time为0，防止更多的请求进来执行
            $this->setNextExcutedTime(0);

            $this->getJobDao()->getConnection()->beginTransaction();
            // 加锁
            $job = $this->getJob($id, true);

            // 并发的时候，一旦有多个请求进来执行同个任务，阻止第２个起的请求执行任务

            if (empty($job) || $job['executing']) {
                $this->getLogService()->error('crontab', 'execute', "任务(#{$job['id']})已经完成或者在执行");
                $this->getJobDao()->getConnection()->commit();
                return;
            }

            $this->getJobDao()->updateJob($job['id'], array('executing' => 1));

            $jobInstance                    = new $job['jobClass']();
            $job['jobParams']['targetType'] = $job['targetType'];
            $job['jobParams']['targetId']   = $job['targetId'];

            $jobInstance->execute($job['jobParams']);

            $this->afterJonExecute($job);

            $this->getJobDao()->getConnection()->commit();

            $this->refreshNextExecutedTime();
        } catch (\Exception $e) {
            $this->afterJonExecute($job);
            $this->getJobDao()->getConnection()->rollback();
            $message = $e->getMessage();
            $this->getLogService()->error('crontab', 'execute', "执行任务(#{$job['id']})失败: {$message}", $job);
            $this->refreshNextExecutedTime();
        }
    }

    public function findJobByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getJobDao()->findJobByTargetTypeAndTargetId($targetType, $targetId);
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nextExcutedStartTime']) && !empty($conditions['nextExcutedEndTime'])) {
            $conditions['nextExcutedStartTime'] = strtotime($conditions['nextExcutedStartTime']);
            $conditions['nextExcutedEndTime']   = strtotime($conditions['nextExcutedEndTime']);
        } else {
            unset($conditions['nextExcutedStartTime']);
            unset($conditions['nextExcutedEndTime']);
        }

        if (empty($conditions['cycle'])) {
            unset($conditions['cycle']);
        }

        if (empty($conditions['name'])) {
            unset($conditions['name']);
        } else {
            $conditions['name'] = '%'.$conditions['name'].'%';
        }

        return $conditions;
    }
}
