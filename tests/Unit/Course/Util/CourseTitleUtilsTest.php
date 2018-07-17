<?php

namespace Tests\Unit\Course\Util;

use Biz\BaseTestCase;
use Biz\Course\Util\CourseTitleUtils;

class CourseTitleUtilsTest extends BaseTestCase
{
    public function testFormatTitleWithEmptyTitle()
    {
        $course = CourseTitleUtils::formatTitle(array('title' => ''), '课程名');
        $this->assertEquals('课程名', $course['title']);
    }

    public function testFormatTitleWithSettedTitle()
    {
        $course = CourseTitleUtils::formatTitle(array('title' => '计划名'), '课程名');
        $this->assertEquals('课程名-计划名', $course['title']);
    }
}
