<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityConfigManager;
use Biz\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ActivityConfigManagerTest extends BaseTestCase
{
    public function testInitClassProdCache()
    {
        new ActivityConfigManager(
            $this->getContainer()->getParameter('kernel.cache_dir'),
            $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/AppBundle/Fixtures/activities',
            false
        );
        $cachePath = $this->getContainer()->getParameter('kernel.cache_dir').'/activities.php';
        $result = include $cachePath;
        $this->assertTrue(isset($result['html_test']));
    }

    public function testIsLtcActivity()
    {
        $activityManager = $this->createActivityManager();
        $this->assertTrue($activityManager->isLtcActivity('html_test'));
        $this->assertFalse($activityManager->isLtcActivity('undefined'));
    }

    public function testGetInstalledActivities()
    {
        $activityManager = $this->createActivityManager();
        $activitiesConf = $activityManager->getInstalledActivities();
        $this->assertTrue(isset($activitiesConf['html_test']));
    }

    public function testGetInstalledActivity()
    {
        $activityManager = $this->createActivityManager();
        $activityConf = $activityManager->getInstalledActivity('html_test');
        $this->assertEquals('html_test', $activityConf['type']);
    }

    protected function createActivityManager()
    {
        return new ActivityConfigManager(
            $this->getContainer()->getParameter('kernel.cache_dir'),
            $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/AppBundle/Fixtures/activities',
            $this->getContainer()->getParameter('kernel.debug')
        );
    }

    public function setUp()
    {
        parent::setUp();
        $filesystem = new Filesystem();
        $filesystem->remove($this->getContainer()->getParameter('kernel.cache_dir'));
        clearstatcache(true);
    }
}
