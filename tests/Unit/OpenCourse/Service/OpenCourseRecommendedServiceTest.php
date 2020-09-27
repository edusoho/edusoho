<?php

namespace Tests\Unit\OpenCourse\Service;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class OpenCourseRecommendedServiceTest extends BaseTestCase
{
    public function testDeleteBatchRecommendCourses()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');
        $this->getCourseRecommendedService()->deleteBatchRecommendCourses([$course1['id']]);
        $result = $this->getCourseRecommendedService()->searchRecommends([], [], 0, \PHP_INT_MAX);
        $this->assertEquals($course2['id'], $result[0]['id']);

        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');
        $this->getCourseRecommendedService()->deleteBatchRecommendCourses([]);
        $result = $this->getCourseRecommendedService()->searchRecommends([], [], 0, \PHP_INT_MAX);
        $this->assertEquals(3, count($result));

        $this->getCourseRecommendedService()->deleteBatchRecommendCourses([$course1['id'], $course2['id']]);
        $result = $this->getCourseRecommendedService()->searchRecommends([], [], 0, \PHP_INT_MAX);
        $this->assertEquals(1, count($result));
    }

    public function testAddRecommendedCourses()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendCourseIds2 = [$course1['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds2, 'course');
        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals(2, count($courses));
        $this->assertEquals($openCourse['id'], $courses[0]['openCourseId']);
        $this->assertEquals($openCourse['id'], $courses[1]['openCourseId']);
        $this->assertEquals($course1['id'], $courses[0]['recommendCourseId']);
        $this->assertEquals($course2['id'], $courses[1]['recommendCourseId']);

        $result = $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], [], 'course');
        $this->assertTrue($result);
    }

    public function testUpdateOpenCourseRecommendedCourses()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $course3 = $this->createCourse('test3');
        $openCourse = $this->createOpenCourse('openCourse1');
        $recommendCourseIds = [$course1['id'], $course2['id'], $course3['id']];
        $recommendCourses = $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'course');

        $activiteRecommendIds = [$recommendCourses[0]['id']];
        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourse['id'], $activiteRecommendIds);

        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals(1, count($courses));

        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourse['id'], []);
        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);
        $this->assertEmpty($courses);
    }

    public function testFindRecommendedCoursesByOpenCourseId()
    {
        $course1 = $this->createCourse('test1');
        $openCourse = $this->createOpenCourse('openCourse');
        $recommendCourseIds = [$course1['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'normal');
        $recommendCourse1 = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals($openCourse['id'], $recommendCourse1[0]['openCourseId']);
        $this->assertEquals($course1['id'], $recommendCourse1[0]['recommendCourseId']);
    }

    public function testSearchRecommendCount()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourseCount = $this->getCourseRecommendedService()->countRecommends(['courseId' => $openCourse['id']]);

        $this->assertEquals(2, $recommendedCourseCount);
    }

    public function testSearchRecommends()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourses = $this->getCourseRecommendedService()->searchRecommends(['courseId' => $openCourse['id']], ['createdTime' => 'DESC'], 0, 2);
        $recommendedCourses = ArrayToolkit::index($recommendedCourses, 'id');

        $this->assertEquals(2, count($recommendedCourses));
        $this->assertEquals($course2['id'], $recommendedCourses[$course2['id']]['recommendCourseId']);
        $this->assertEquals($course1['id'], $recommendedCourses[$course1['id']]['recommendCourseId']);
    }

    public function testRecommendedCoursesSort()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendCourses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);
        $recommendCourses = $this->getCourseRecommendedService()->recommendedCoursesSort($recommendCourses);

        $this->assertEquals($course1['title'], $recommendCourses[$course1['id']]['title']);
        $this->assertEquals($course2['title'], $recommendCourses[$course2['id']]['title']);
    }

    public function testFindRandomRecommendCourses()
    {
        $openCourse = $this->createOpenCourse('公开课1');
        $courseIds = [];
        foreach (range(1, 10) as $i) {
            $course = $this->createCourse('course'.$i);
            $courseIds[] = $course['id'];
        }
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $courseIds, 'course');
        $needNum = 5;
        $randomCourses = $this->getCourseRecommendedService()->findRandomRecommendCourses($openCourse['id'], $needNum);

        $this->assertEquals(count($randomCourses), $needNum);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testFindRandomRecommendCoursesError()
    {
        $this->getCourseRecommendedService()->findRandomRecommendCourses(1, -1);
    }

    public function testDeleteRecommendCourse()
    {
        $result = $this->getCourseRecommendedService()->deleteRecommendCourse(1);
        $this->assertTrue($result);

        $fields = [
            'recommendCourseId' => 1,
            'openCourseId' => 1,
            'type' => 'normal',
        ];
        $recommendCourse = $this->getRecommendedCourseDao()->create($fields);
        $this->assertNotNull($recommendCourse);

        $this->getCourseRecommendedService()->deleteRecommendCourse($recommendCourse['id']);
        $result = $this->getCourseRecommendedService()->getRecommendedCourseByCourseIdAndType(1, $recommendCourse['id'], 'normal');

        $this->assertNull($result);
    }

    public function testAddRecommendeds()
    {
        $recommendedCourseDao = $this->mockBiz(
            'OpenCourse:OpenCourseRecommendedDao',
            [
                [
                    'functionName' => 'create',
                    'withParamms' => [
                        [
                            'recommendCourseId' => 12,
                            'openCourseId' => 123,
                            'type' => 'live',
                        ],
                    ],
                    'times' => 1,
                ],
            ]
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getCourseRecommendedService(),
            'addRecommendeds',
            [[12], 123, 'live']
        );
        $this->assertTrue($result);
        $recommendedCourseDao->shouldHaveReceived('create')->times(1);
    }

    protected function createCourse($title)
    {
        $course = [
            'title' => $title,
            'type' => 'normal',
            'courseSetId' => '1',
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
        ];
        $createCourse = $this->getCourseSetService()->createCourseSet($course);

        return $createCourse;
    }

    protected function createOpenCourse($title)
    {
        $course = [
            'title' => $title,
            'type' => 'open',
        ];

        $createCourse = $this->getOpenCourseService()->createCourse($course);

        return $createCourse;
    }

    protected function getRecommendedCourseDao()
    {
        return $this->createDao('OpenCourse:OpenCourseRecommendedDao');
    }

    /**
     * @return OpenCourseRecommendedService
     */
    protected function getCourseRecommendedService()
    {
        return $this->createService('OpenCourse:OpenCourseRecommendedService');
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
