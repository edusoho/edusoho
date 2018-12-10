<?php

namespace Tests\Unit\Course\Accessor;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Biz\Course\Accessor\LearnCourseTaskAccessor;

class LearnCourseTaskAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new LearnCourseTaskAccessor($this->getBiz());
        $result = $accessor->access(array());
        $this->assertEquals('NOTFOUND_TASK', $result['code']);

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => true,
                    'withParams' => array('course.allowAnonymousPreview', 1),
                ),
            )
        );
        $this->createDemoCourse();
        $result = $accessor->access(array('courseId' => 1, 'isFree' => 1));
        $this->assertNull($result);
    }

    public function testTryFreeLearn()
    {
        $this->mockBiz(
            'Activity:ActivityService',
            array(
                array(
                    'functionName' => 'getActivity',
                    'returnValue' => array('ext' => array('file' => array('storage' => 'cloud'))),
                    'withParams' => array(1, true),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getActivity',
                    'returnValue' => array('ext' => array('file' => array('storage' => 'local'))),
                    'withParams' => array(1, true),
                    'runTimes' => 1,
                ),
            )
        );
        $accessor = new LearnCourseTaskAccessor($this->getBiz());
        $task = array('isFree' => 0, 'type' => 'video', 'activityId' => 1);
        $result = ReflectionUtils::invokeMethod($accessor, 'tryFreeLearn', array($task, array('tryLookable' => 1), 'testCode'));
        $this->assertEquals('allow_trial', $result['code']);

        $result = ReflectionUtils::invokeMethod($accessor, 'tryFreeLearn', array($task, array('tryLookable' => 1), 'testCode'));
        $this->assertEquals('testCode', $result['code']);
    }

    protected function createDemoCourse()
    {
        $course = array(
            'title' => '教学计划Demo-'.rand(0, 1000),
            'courseSetId' => 1,
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );

        return $this->getCourseService()->createCourse($course);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
