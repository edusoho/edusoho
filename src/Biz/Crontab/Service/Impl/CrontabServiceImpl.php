<?php

namespace Biz\Crontab\Service\Impl;

use Biz\BaseService;
use Biz\Crontab\Dao\JobDao;
use Symfony\Component\Yaml\Yaml;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\LogService;
use Biz\Crontab\Service\CrontabService;
use Codeages\Biz\Framework\Context\BizAware;
use Topxia\Service\Common\ServiceKernel;

class CrontabServiceImpl extends BaseService implements CrontabService
{
    public function getJob($id, $lock = false)
    {
        return $this->getJobDao()->get($id, array('lock' => $lock));
    }

    public function executeJob($id)
    {
        $job = array();
        // 开始执行job的时候，设置next_executed_time为0，防止更多的请求进来执行
        $this->setNextExcutedTime(0);
        $result = $this->syncronizeUpdateExecutingStatus($id);
        if (!$result) {
            return;
        }

        $this->beginTransaction();
        $throws = null;
        try {
            // 加锁
            $job = $this->getJob($id, true);

            $jobInstance = new $job['jobClass']();

            if ($jobInstance instanceof BizAware) {
                $jobInstance->setBiz($this->biz);
            }

            if (!empty($job['targetType'])) {
                $job['jobParams']['targetType'] = $job['targetType'];
            }

            if (!empty($job['targetId'])) {
                $job['jobParams']['targetId'] = $job['targetId'];
            }

            $jobInstance->execute($job['jobParams']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->updateJob($job['id'], array('executing' => 0));
            $this->getLogService()->error('crontab', 'execute', "执行任务(#{$job['id']})失败: {$message}", $job);
            $throws = $e;
        }

        $this->afterJonExecute($job);
        $this->commit();
        $this->refreshNextExecutedTime();

        if (null !== $throws) {
            throw $throws;
        }
    }

    protected function afterJonExecute($job)
    {
        if ($job['cycle'] == 'once') {
            $this->getJobDao()->delete($job['id']);
        }
        if ($job['cycle'] == 'everyhour') {
            $time = time();
            $this->getJobDao()->update($job['id'], array(
                'executing' => '0',
                'latestExecutedTime' => $time,
                'nextExcutedTime' => strtotime('+1 hours', $time),
            ));
        }
        if ($job['cycle'] == 'everyday') {
            $time = time();
            $this->getJobDao()->update($job['id'], array(
                'executing' => '0',
                'latestExecutedTime' => $time,
                'nextExcutedTime' => strtotime(date('Y-m-d', strtotime('+1 day', $time)).' '.$job['cycleTime']),
            ));
        }
    }

    private function syncronizeUpdateExecutingStatus($id)
    {
        // 并发的时候，一旦有多个请求进来执行同个任务，阻止第２个起的请求执行任务
        $lockName = "job_{$id}";
        $lock = $this->getLock();
        $lock->get($lockName, 10);

        $job = $this->getJob($id);
        if (empty($job) || $job['executing']) {
            $this->getLogService()->error('crontab', 'execute', "任务(#{$job['id']})已经完成或者在执行");
            $lock->release($lockName);

            return false;
        }

        $this->getJobDao()->update($job['id'], array('executing' => 1));
        $lock->release($lockName);

        return true;
    }

    public function searchJobs($conditions, $sort, $start, $limit)
    {
        switch ($sort) {
            case 'created':
                $sort = array('createdTime' => 'DESC');
                break;
            case 'createdByAsc':
                $sort = array('createdTime' => 'ASC');
                break;
            case 'nextExcutedTime':
                $sort = array('nextExcutedTime' => 'DESC');
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
                break;
        }
        $jobs = $this->getJobDao()->search($conditions, $sort, $start, $limit);

        return $jobs;
    }

    public function searchJobsCount($conditions)
    {
        return $this->getJobDao()->count($conditions);
    }

    public function createJob($job)
    {
        $user = $this->getCurrentUser();

        if (!ArrayToolkit::requireds($job, array('nextExcutedTime'))) {
            throw $this->createInvalidArgumentException('Field nextExcutedTime Required');
        }

        $job['creatorId'] = $user['id'];
        $job['createdTime'] = time();

        $job = $this->getJobDao()->create($job);

        $this->refreshNextExecutedTime();

        return $job;
    }

    public function deleteJob($id)
    {
        $deleted = $this->getJobDao()->delete($id);
        $this->refreshNextExecutedTime();

        return $deleted;
    }

    public function deleteJobs($targetId, $targetType)
    {
        $deleted = $this->getJobDao()->deleteByTargetTypeAndTargetId($targetType, $targetId);
        $this->refreshNextExecutedTime();

        return $deleted;
    }

    public function scheduleJobs()
    {
        $conditions = array(
            'executing' => 0,
            'enabled' => 1,
            'nextExcutedTime' => time(),
        );
        $job = $this->getJobDao()->search($conditions, array('nextExcutedTime' => 'ASC'), 0, 1);

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
            'executing' => 0,
        );

        $job = $this->getJobDao()->search($conditions, array('nextExcutedTime' => 'ASC'), 0, 1);

        if (empty($job)) {
            $this->setNextExcutedTime(0);
        } else {
            $this->setNextExcutedTime($job[0]['nextExcutedTime']);
        }
    }

    public function getNextExcutedTime()
    {
        $filePath = $this->getCrontabConfigYml();
        $yaml = new Yaml();

        if (!file_exists($filePath)) {
            $content = $yaml->dump(array('crontab_next_executed_time' => 0));
            $fh = fopen($filePath, 'w');
            fwrite($fh, $content);
            fclose($fh);
        }

        $fileContent = file_get_contents($filePath);
        $config = $yaml->parse($fileContent);

        return $config['crontab_next_executed_time'];
    }

    public function setNextExcutedTime($nextExcutedTime)
    {
        $filePath = $this->getCrontabConfigYml();
        $yaml = new Yaml();
        $content = $yaml->dump(array('crontab_next_executed_time' => $nextExcutedTime));
        $fh = fopen($filePath, 'w');
        fwrite($fh, $content);
        fclose($fh);
    }

    private function getCrontabConfigYml()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/data/crontab_config.yml';
    }

    public function findJobByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getJobDao()->findByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function findJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId)
    {
        return $this->getJobDao()->findByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
    }

    public function updateJob($id, $fields)
    {
        return $this->getJobDao()->update($id, $fields);
    }

    /**
     * @return JobDao
     */
    protected function getJobDao()
    {
        return $this->createDao('Crontab:JobDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
