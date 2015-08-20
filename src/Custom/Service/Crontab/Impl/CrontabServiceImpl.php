<?php
namespace Custom\Service\Crontab\Impl;

use Topxia\Service\Crontab\CrontabService;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Crontab\Impl\CrontabServiceImpl as BaseCrontabService;

class CrontabServiceImpl extends BaseCrontabService implements CrontabService
{
    public function executeJob($id)
    {
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

            $jobInstance = new $job['jobClass']();
            $jobInstance->execute($job['jobParams']);

            if ($job['cycle'] == 'once') {
                $this->getJobDao()->deleteJob($job['id']);
            }

            $time = time();
            if ($job['cycle'] == 'everyminute') {
                $this->getJobDao()->updateJob($job['id'], array(
                    'executing' => '0',
                    'latestExecutedTime' => $time,
                    'nextExcutedTime' => strtotime('+1 minutes',$time);
                ));
            }

            if ($job['cycle'] == 'everyhour') {
                $this->getJobDao()->updateJob($job['id'], array(
                    'executing' => '0',
                    'latestExecutedTime' => $time,
                    'nextExcutedTime' => strtotime('+1 hours',$time)
                ));
            }

            if ($job['cycle'] == 'everyday') {
                $this->getJobDao()->updateJob($job['id'], array(
                    'executing' => '0',
                    'latestExecutedTime' => $time,
                    'nextExcutedTime' => strtotime(date('Y-m-d', strtotime('+1 day',$time)).' '.$job['cycleTime'])
                ));
            }

            $this->getJobDao()->getConnection()->commit();

            $this->refreshNextExecutedTime();

        } catch(\Exception $e) {
            $this->getJobDao()->getConnection()->rollback();
            $message = $e->getMessage();
            $this->getLogService()->error('crontab', 'execute', "执行任务(#{$job['id']})失败: {$message}");
            $this->refreshNextExecutedTime();
        }
    }
}
