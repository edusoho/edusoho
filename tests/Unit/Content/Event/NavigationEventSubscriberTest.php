<?php

namespace Tests\Unit\Content\Event;

use Biz\BaseTestCase;
use Biz\Content\Event\NavigationEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class NavigationEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
            'navigation.operate' => 'onNavigationOperate',
        ), NavigationEventSubscriber::getSubscribedEvents());
    }

    public function testOnNavigationOperate()
    {
        $service = $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'set',
            ),
        ));

        $event = new Event(array('type' => 'top'));
        $navigationEventSubscriber = new NavigationEventSubscriber($this->biz);
        $navigationEventSubscriber->onNavigationOperate($event);

        $service->shouldHaveReceived('get')->times(1);
        $service->shouldHaveReceived('set')->times(1);
    }
}
