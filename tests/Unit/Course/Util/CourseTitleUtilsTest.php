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

    public function testGetDisplayedTitle()
    {
        $displayedTitle = CourseTitleUtils::getDisplayedTitle(
            array('title' => '计划名', 'courseSetTitle' => '课程名')
        );
        $this->assertEquals('课程名-计划名', $displayedTitle);
    }

    public function testGetDisplayedTitleWithEmptyTitle()
    {
        $displayedTitle = CourseTitleUtils::getDisplayedTitle(
            array('title' => '', 'courseSetTitle' => '课程名')
        );
        $this->assertEquals('课程名', $displayedTitle);
    }

    public function testGetDisplayedTitleWithEmptyCourseSetTitle()
    {
        $displayedTitle = CourseTitleUtils::getDisplayedTitle(
            array('title' => '计划名', 'courseSetTitle' => '')
        );
        $this->assertNull($displayedTitle);
    }
}
