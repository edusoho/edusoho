<?php
namespace Topxia\Service\Crontab\Impl;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Crontab\CrontabService;

class CrontabServiceImpl extends BaseService implements CrontabService
{
    public function getJob($id)
    {
        return $this->getJobDao()->getJob($id);
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

    public function createJob($job)
    {
        $user = $this->getCurrentUser();

        if (!ArrayToolKit::requireds($job, array('nextExcutedTime'))) {
            throw $this->createServiceException('缺少 nextExcutedTime 字段,创建job失败');
        }

        $job['creatorId']   = $user['id'];
        $job['createdTime'] = time();

        $job = $this->getJobDao()->addJob($job);

        $this->refreshNextExecutedTime();

        return $job;
    }

    public function executeJob($id)
    {
        $job = array();
        // 开始执行job的时候，设置next_executed_time为0，防止更多的请求进来执行
        $this->setNextExcutedTime(0);
        $this->getJobDao()->getConnection()->beginTransaction();

        try {
            // 加锁
            $job = $this->getJob($id, true);

// 并发的时候，一旦有多个请求进来执行同个任务，阻止第２个起的请求执行任务

            if (empty($job) || $job['executing']) {
                $this->getLogService()->error('crontab', 'execute', "任务(#{$job['id']})已经完成或者在执行");
                $this->getJobDao()->getConnection()->commit();
                return;
            }

            $this->getJobDao()->updateJob($job['id'], array('executing' => 1));
            $jobInstance = new $job['jobClass']();
            if (!empty($job['targetType'])) {
                $job['jobParams']['targetType'] = $job['targetType'];
            }

            if (!empty($job['targetId'])) {
                $job['jobParams']['targetId'] = $job['targetId'];
            }

            $jobInstance->execute($job['jobParams']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->getLogService()->error('crontab', 'execute', "执行任务(#{$job['id']})失败: {$message}", $job);
        }

        $this->afterJonExecute($job);
        $this->getJobDao()->getConnection()->commit();
        $this->refreshNextExecutedTime();
    }

    protected function afterJonExecute($job)
    {
        if ($job['cycle'] == 'once') {
            $this->getJobDao()->deleteJob($job['id']);
        }

        if ($job['cycle'] == 'everyhour') {
            $time = time();
            $this->getJobDao()->updateJob($job['id'], array(
                'executing'          => '0',
                'latestExecutedTime' => $time,
                'nextExcutedTime'    => strtotime('+1 hours', $time)
            ));
        }

        if ($job['cycle'] == 'everyday') {
            $time = time();
            $this->getJobDao()->updateJob($job['id'], array(
                'executing'          => '0',
                'latestExecutedTime' => $time,
                'nextExcutedTime'    => strtotime(date('Y-m-d', strtotime('+1 day', $time)).' '.$job['cycleTime'])
            ));
        }
    }

    public function deleteJob($id)
    {
        $deleted = $this->getJobDao()->deleteJob($id);
        $this->refreshNextExecutedTime();
        return $deleted;
    }

    public function deleteJobs($targetId, $targetType)
    {
        $deleted = $this->getJobDao()->deleteJobs($targetId, $targetType);
        $this->refreshNextExecutedTime();
        return $deleted;
    }

    public function scheduleJobs()
    {
        $conditions = array(
            'executing'       => 0,
            'nextExcutedTime' => time()
        );
        $job = $this->getJobDao()->searchJobs($conditions, array('nextExcutedTime', 'ASC'), 0, 1);

        if (!empty($job)) {
            $job = $job[0];
            $this->getLogService()->info('crontab', 'job_start', "定时任务(#{$job['id']})开始执行！", $job);
            $this->executeJob($job['id']);
            $this->getLogService()->info('crontab', 'job_end', "定时任务(#{$job['id']})执行结束！", $job);
        }
    }

    protected function refreshNextExecutedTime()
    {
        $conditions = array(
            'executing' => 0
        );

        $job = $this->getJobDao()->searchJobs($conditions, array('nextExcutedTime', 'ASC'), 0, 1);

        if (empty($job)) {
            $this->setNextExcutedTime(0);
        } else {
            $this->setNextExcutedTime($job[0]['nextExcutedTime']);
        }
    }

    public function getNextExcutedTime()
    {
        $filePath = __DIR__.'/../../../../../app/data/crontab_config.yml';
        $yaml     = new Yaml();

        if (!file_exists($filePath)) {
            $content = $yaml->dump(array('crontab_next_executed_time' => 0));
            $fh      = fopen($filePath, "w");
            fwrite($fh, $content);
            fclose($fh);
        }

        $fileContent = file_get_contents($filePath);
        $config      = $yaml->parse($fileContent);

        return $config['crontab_next_executed_time'];
    }

    public function setNextExcutedTime($nextExcutedTime)
    {
        $filePath = __DIR__.'/../../../../../app/data/crontab_config.yml';
        $yaml     = new Yaml();
        $content  = $yaml->dump(array('crontab_next_executed_time' => $nextExcutedTime));
        $fh       = fopen($filePath, "w");
        fwrite($fh, $content);
        fclose($fh);
    }

    public function findJobByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getJobDao()->findJobByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function findJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId)
    {
        return $this->getJobDao()->findJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
    }

    public function updateJob($id, $fields)
    {
        return $this->getJobDao()->updateJob($id, $fields);
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

    protected function getJobDao()
    {
        return $this->createDao('Crontab.JobDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}
