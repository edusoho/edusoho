<?php

namespace Tests\Unit\Course\Accessor;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Course\Accessor\LearnCourseTaskAccessor;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class LearnCourseTaskAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new LearnCourseTaskAccessor($this->getBiz());
        $result = $accessor->access([]);
        self::assertEquals('course.task.not_found', $result['code']);

        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => true,
                    'withParams' => ['course.allowAnonymousPreview', 1],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'get',
                    'returnValue' => [],
                    'withParams' => ['security'],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'get',
                    'withParams' => ['site', []],
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => ['magic'],
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => ['coin', []],
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => ['cloud_search', []],
                    'returnValue' => [],
                ],
            ]
        );
        $this->createDemoCourse();
        $result = $accessor->access(['courseId' => 1, 'isFree' => 1]);
        self::assertNull($result);
    }

    public function testTryFreeLearn()
    {
        $this->mockBiz(
            'Activity:ActivityService',
            [
                [
                    'functionName' => 'getActivity',
                    'returnValue' => ['ext' => ['file' => ['storage' => 'cloud']]],
                    'withParams' => [1, true],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'getActivity',
                    'returnValue' => ['ext' => ['file' => ['storage' => 'local']]],
                    'withParams' => [1, true],
                    'runTimes' => 1,
                ],
            ]
        );
        $accessor = new LearnCourseTaskAccessor($this->getBiz());
        $task = ['isFree' => 0, 'type' => 'video', 'activityId' => 1];
        $result = ReflectionUtils::invokeMethod($accessor, 'tryFreeLearn', [$task, ['tryLookable' => 1], 'testCode']);
        self::assertEquals('allow_trial', $result['code']);

        $result = ReflectionUtils::invokeMethod($accessor, 'tryFreeLearn', [$task, ['tryLookable' => 1], 'testCode']);
        self::assertEquals('testCode', $result['code']);
    }

    protected function createDemoCourse()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet([
            'title' => 'testCourseSet',
            'type' => 'normal',
        ]);
        $course = [
            'title' => '教学计划Demo-'.rand(0, 1000),
            'courseSetId' => $courseSet['id'],
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        ];

        return $this->getCourseService()->createCourse($course);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
