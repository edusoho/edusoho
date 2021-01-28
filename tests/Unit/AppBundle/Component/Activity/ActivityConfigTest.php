<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityConfig;
use Biz\BaseTestCase;

class ActivityConfigTest extends BaseTestCase
{
    public function testOffsetExists()
    {
        $activityConfig = new ActivityConfig(array('type' => 'html'));
        $this->assertTrue($activityConfig->offsetExists('type'));
    }

    public function testOffsetGet()
    {
        $activityConfig = new ActivityConfig(array('type' => 'html'));
        $this->assertEquals('html', $activityConfig->offsetGet('type'));
        $this->assertEquals('html', $activityConfig['type']);
    }

    /**
     * @expectedException \AppBundle\Common\Exception\UnexpectedValueException
     */
    public function testOffsetGetWithNotExist()
    {
        $config = new ActivityConfig(array());
        $config->offsetGet('test');
    }

    public function testOffsetSet()
    {
        $activityConfig = new ActivityConfig(array());
        $this->assertEquals('html', $activityConfig->offsetSet('type', 'html')->offsetGet('type'));
    }

    public function testOffsetUnset()
    {
        $activityConfig = new ActivityConfig(array());
        $this->assertEquals('html', $activityConfig->offsetSet('type', 'html')->offsetGet('type'));
        $activityConfig->offsetUnSet('type');
        $this->assertFalse($activityConfig->offsetExists('type'));
    }
}
