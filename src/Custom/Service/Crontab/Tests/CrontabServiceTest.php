<?php
namespace Custom\Service\Homework\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Doctrine\DBAL\Query\QueryBuilder;


class CrontabServiceTest extends BaseTestCase
{
    public function testExecuteJbo(){
        $job=$this->getCrontabService()->createJob(array(
            'name'=>'ForwardHomeworkStatusJob',
            'jobClass'=>'Custom\Service\Homework\Job\ForwardHomeworkStatusJob',
            'cycle'=>'everyminute',
            'cycleTime'=>'0',
            'jobParams'=>'',
            'executing'=>'0',
            'nextExcutedTime'=>'0',
            'latestExecutedTime'=>'0',
            'creatorId'=>'0',
            'createdTime'=>'0'
        ));
        $this->getCrontabService()->executeJob($job['id']);
    }

    protected function getCrontabService(){
        return $this->getServiceKernel()->createService('Custom:Crontab.CrontabService');
    }
}