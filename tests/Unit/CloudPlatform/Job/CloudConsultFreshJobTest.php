<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\CloudPlatform\Job\CloudConsultFreshJob;
use Biz\System\Service\SettingService;

class CloudConsultFreshJobTest extends BaseTestCase
{
    public function testExcuteWhitEmptyCloudConsult()
    {
        $job = new CloudConsultFreshJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);
    }

    public function testExcute()
    {
        $this->getSettingService()->set('cloud_consult', array('cloud_consult_setting_enabled' => 1));
        $job = new CloudConsultFreshJob(array(), $this->biz);
        $job->execute();
        $result = $this->getSettingService()->get('cloud_consult', array());

        $this->assertEquals($result['cloud_consult_setting_enabled'], 1);
        $this->assertEquals($result['cloud_consult_code'], 0);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
