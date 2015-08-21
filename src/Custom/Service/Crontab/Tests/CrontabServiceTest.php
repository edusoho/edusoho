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
        $this->getCrontabService()->executeJob(3);
    }

    protected function getCrontabService(){
        return $this->getServiceKernel()->createService('Custom:Crontab.CrontabService');
    }
}