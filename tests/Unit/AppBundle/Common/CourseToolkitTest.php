<?php

namespace Tests\Unit\AppBundle\Common\Tests;

use AppBundle\Common\CourseToolkit;
use Biz\BaseTestCase;

class CourseToolkitTest extends BaseTestCase
{
    public function testGetUserDisplayedChapterTypes()
    {
        $result = CourseToolkit::getUserDisplayedChapterTypes();

        $this->assertEquals(2, count($result));
        $this->assertArrayEquals(array('chapter', 'unit'), $result);
    }

    public function testGetAvailableChapterTypes()
    {
        $result = CourseToolkit::getAvailableChapterTypes();

        $this->assertEquals(3, count($result));
        $this->assertArrayEquals(array('chapter', 'unit', 'lesson'), $result);
    }
}
