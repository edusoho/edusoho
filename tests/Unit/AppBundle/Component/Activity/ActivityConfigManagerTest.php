<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityConfigManager;
use Biz\BaseTestCase;

class ActivityConfigManagerTest extends BaseTestCase
{
    public function testInitClassProdCache()
    {
        new ActivityConfigManager($this->getContainer()->getParameter('kernel.cache_dir'), $this->getContainer()->getParameter('edusoho.activities_dir'), false);
        $cachePath = $this->getContainer()->getParameter('kernel.cache_dir').'/activities.php';
        $result = include $cachePath;
        $this->assertTrue(isset($result['html_test']));
    }

    public function testIsLtcActivity()
    {
        $activityManager = $this->getContainer()->get('activity_config_manager');
        $this->assertTrue($activityManager->isLtcActivity('html_test'));
        $this->assertFalse($activityManager->isLtcActivity('undefined'));
    }

    public function testGetInstalledActivities()
    {
        $activityManager = $this->getContainer()->get('activity_config_manager');
        $activitiesConf = $activityManager->getInstalledActivities();
        $this->assertTrue(isset($activitiesConf['html_test']));
    }

    public function testGetInstalledActivity()
    {
        $activityManager = $this->getContainer()->get('activity_config_manager');
        $activityConf = $activityManager->getInstalledActivity('html_test');
        $this->assertEquals('html_test', $activityConf['type']);
    }
}
