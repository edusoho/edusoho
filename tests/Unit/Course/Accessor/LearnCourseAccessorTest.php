<?php

namespace Tests\Unit\Course\Accessor;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Biz\Course\Accessor\LearnCourseAccessor;

class LearnCourseAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new LearnCourseAccessor($this->getBiz());
        $result = $accessor->access(array());
        $this->assertEquals('course.not_found', $result['code']);

        $result = $accessor->access(array('status' => 'draft', 'id' => 1));
        $this->assertEquals('course.unpublished', $result['code']);

        $result = $accessor->access(array('status' => 'published', 'expiryMode' => 'date', 'expiryEndDate' => 0, 'id' => 1));
        $this->assertEquals('course.expired', $result['code']);

        $result = $accessor->access(array('status' => 'published', 'expiryMode' => 'date', 'expiryStartDate' => time() + 5000, 'expiryEndDate' => time() + 5000, 'id' => 1));
        $this->assertEquals('course.not_arrive', $result['code']);

        $result = $accessor->access(array('status' => 'published', 'expiryMode' => 'date', 'expiryStartDate' => 0, 'expiryEndDate' => time() + 5000, 'id' => 1));
        $this->assertNull($result);
    }

    public function testIsExpired()
    {
        $accessor = new LearnCourseAccessor($this->getBiz());
        $result = ReflectionUtils::invokeMethod($accessor, 'isExpired', array(array('expiryMode' => 'forever')));
        $this->assertFalse($result);

        $result = ReflectionUtils::invokeMethod($accessor, 'isExpired', array(array('expiryMode' => 'mode')));
        $this->assertFalse($result);
    }
}