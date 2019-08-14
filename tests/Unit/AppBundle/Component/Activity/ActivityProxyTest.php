<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityConfig;
use AppBundle\Component\Activity\ActivityContext;
use AppBundle\Component\Activity\ActivityProxy;
use Biz\BaseTestCase;

class ActivityProxyTest extends BaseTestCase
{
    public function testGetActivityConfig()
    {
        list($activityContainer, $activityConfig, $activityProxy) = $this->createActivityMeta();
        $result = $activityProxy->getActivityConfig();
        $this->assertEquals($activityConfig, $result);
    }

    public function testGetActivityContext()
    {
        list($activityContainer, $activityConfig, $activityProxy) = $this->createActivityMeta();
        $result = $activityProxy->getActivityContext();
        $this->assertTrue($result instanceof ActivityContext);
    }

    /**
     * @expectedException \AppBundle\Common\Exception\InvalidArgumentException
     * @expectedExceptionMessage Bad file extension in routes, please check.
     */
    public function testRenderRouteWithException()
    {
        list($activityContainer, $activityConfig, $activityProxy) = $this->createActivityMeta();
        $activityProxy->renderRoute('invalid');
    }

    public function testRenderRouteWithUndefinedException()
    {
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        list($activityContainer, $activityConfig, $activityProxy) = $this->createActivityMeta($activity);
        $activityProxy->renderRoute('undefined', array('activity' => $activity));
    }

    protected function createActivityMeta($activity = array())
    {
        $activity = array_merge(array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        ), $activity);
        $activityContainer = $this->getContainer()->get('activity_runtime_container');
        $activityConfig = new ActivityConfig($this->getContainer()->get('activity_config_manager')->getInstalledActivity($activity['mediaType']));
        $activityProxy = new ActivityProxy($activityContainer, $activity, $activityConfig);

        return array($activityContainer, $activityConfig, $activityProxy);
    }
}
