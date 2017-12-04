<?php

namespace Tests\Unit\Activity;

use Biz\BaseTestCase;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Activity\Service\ActivityService;
use AppBundle\Common\ReflectionUtils;

class ActivityTestpaperCopyTest extends BaseTestCase
{
    public function testCopyEntity()
    {
        $testpaper = $this->createTestpaper();
        $activity = $this->createActivityAndMedia();
        $config = array(
            'newCourseId' => '2',
            'newCourseSetId' => '1',
            'isCopy' => true,
        );

        $newTestpaper = ReflectionUtils::invokeMethod(
            $this->getActivityTestpaperCopy(),
            'copyEntity',
            array($activity, $config)
        );

        $this->assertEquals($testpaper['id'], $newTestpaper['id']);

        $config = array(
            'newCourseId' => '2',
            'newCourseSetId' => '2',
            'isCopy' => true,
        );

        $newTestpaper = ReflectionUtils::invokeMethod(
            $this->getActivityTestpaperCopy(),
            'copyEntity',
            array($activity, $config)
        );

        $this->assertEquals(
            array('courseId' => '2', 'copyId' => '22', 'type'=> 'testpaper'),
            array('courseId' => $newTestpaper['courseId'], 'copyId' => $newTestpaper['copyId'], 'type'=> $newTestpaper['type'])
        );
    }

    protected function getActivityTestpaperCopy()
    {
        return new \Biz\Course\Copy\Chain\ActivityTestpaperCopy($this->biz);
    }

    protected function createActivityAndMedia()
    {
        $fields = array(
            'id' => 1,
            'mediaId' => 22,
        );
        $this->getTestpaperActivityDao()->create($fields);

        $fields = array(
            'id' => 1,
            'title' => 'testpaper',
            'mediaId' => '1',
            'mediaType' => 'testpaper',
            'fromCourseId' => '1',
            'fromCourseSetId' => '1',
        );

        return $this->getActivityDao()->create($fields);
    }

    protected function createTestpaper()
    {
        $testpaperFields = array(
            'id' => 22,
            'name' => 'testpaper',
            'courseId' => 1,
            'lessonId' => 1,
            'limitedTime' => 0,
            'pattern' => 'questionType',
            'copyId' => 0,
            'type' => 'testpaper',
            'courseSetId' => 1,
        );
        return $this->getTestpaperService()->createTestpaper($testpaperFields);
    }

    protected function getTestpaperActivityDao()
    {
        return $this->createDao('Activity:TestpaperActivityDao');
    }

    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

}
