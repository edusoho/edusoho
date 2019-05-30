<?php

namespace Tests\Unit\System\Service;

use Biz\System\Service\H5SettingService;
use Biz\BaseTestCase;

class H5SettingServiceTest extends BaseTestCase
{
    public function testGetDiscovery()
    {
        $discoverySettings = $this->getH5SettingService()->getDiscovery('h5');
        $this->assertEquals('slide_show', $discoverySettings['slide-1']['type']);
        $this->assertEquals('course_list', $discoverySettings['courseList-1']['type']);
    }

    public function testFilter()
    {
        $discoverySettings = array(
            'slide-1' => array(
                'type' => 'slide_show',
                'moduleType' => 'slide-1',
                'data' => array(
                    'title' => '',
                    'image' => array(
                        'id' => 0,
                        'uri' => '',
                        'size' => '',
                        'createdTime' => 0,
                    ),
                    'link' => array(
                        'type' => 'url',
                        'target' => null,
                        'url' => '',
                    ),
                ),
            )
        );

        $discoverySettings = $this->getH5SettingService()->filter($discoverySettings);
        $this->assertEquals('slide_show', $discoverySettings['slide-1']['type']);
    }

    public function testCourseListFilter()
    {
        $discoverySetting = array(
            'type' => 'course_list',
            'data' => array(
                'sourceType' => 'condition',
                'categoryId' => 0,
                'sort' => '-studentNum',
                'lastDays' => 0,
                'limit' => 1,
                'items' => array(),
            ),
        );
        $discoverySetting = $this->getH5SettingService()->courseListFilter($discoverySetting);

        $this->assertEquals('course_list', $discoverySetting['type']);
        $this->assertEquals('condition', $discoverySetting['data']['sourceType']);

        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('course title 1', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);

        $course = $this->defaultCourse('course title 2', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);

        $discoverySetting = array(
            'type' => 'course_list',
            'data' => array(
                'sourceType' => 'custom',
                'items' => array(
                    0 => array('id' => 1),
                    1 => array('id' => 2),
                    2 => array('id' => 3)
                ),
            ),
        );
        $discoverySetting = $this->getH5SettingService()->courseListFilter($discoverySetting);
        $this->assertEmpty($discoverySetting['data']['items'][0]);
        $this->assertEmpty($discoverySetting['data']['items'][1]);
        $this->assertEmpty($discoverySetting['data']['items'][2]);
        $this->assertEquals('custom', $discoverySetting['data']['sourceType']);
    }

    public function testClassroomListFilter()
    {
        $discoverySetting = array(
            'type' => 'classroom_list',
            'data' => array(
                'sourceType' => 'condition',
                'categoryId' => 0,
                'sort' => '-studentNum',
                'lastDays' => 0,
                'limit' => 1,
                'items' => array(),
            ),
        );
        $discoverySetting = $this->getH5SettingService()->classroomListFilter($discoverySetting);

        $this->assertEquals('classroom_list', $discoverySetting['type']);
        $this->assertEquals('condition', $discoverySetting['data']['sourceType']);

        $textClassroom = array(
            'title' => 'test001',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $discoverySetting = array(
            'type' => 'course_list',
            'data' => array(
                'sourceType' => 'custom',
                'items' => array(
                    0 => array('id' => 1),
                    1 => array('id' => 2),
                    2 => array('id' => 3)
                ),
            ),
        );
        $discoverySetting = $this->getH5SettingService()->classroomListFilter($discoverySetting);
        $this->assertEmpty($discoverySetting['data']['items'][0]);
        $this->assertEmpty($discoverySetting['data']['items'][1]);
        $this->assertEmpty($discoverySetting['data']['items'][2]);
        $this->assertEquals('custom', $discoverySetting['data']['sourceType']);
    }

    public function testSlideShowFilter()
    {

    }

    public function testPosterFilter()
    {

    }

    public function testGrouponFilter()
    {

    }

    public function testSeckillFilter()
    {

    }

    public function testCutFilter()
    {

    }

    public function testCouponFilter()
    {

    }

    public function testVipFilter()
    {

    }

    public function testGetCourseCondition()
    {

    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    protected function defaultCourse($title, $courseSet, $isDefault = 1)
    {
        return array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => $isDefault,
            'courseType' => $isDefault ? 'default' : 'normal',
        );
    }

    protected function createNewCourse($courseSetId)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array($courseSetId));

        if (empty($courses)) {
            $courseFields = array(
                'title' => '第一个教学计划',
                'courseSetId' => 1,
                'learnMode' => 'lockMode',
                'expiryDays' => 0,
                'expiryMode' => 'forever',
            );

            $course = $this->getCourseService()->createCourse($courseFields);
        } else {
            $course = $courses[0];
        }

        $this->assertNotEmpty($course);

        return $course;
    }

    private function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}