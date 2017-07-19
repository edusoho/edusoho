<?php

namespace Tests\Unit\Course\Sync;

use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Sync\Service\AbstractSychronizer;
use Biz\Sync\Service\SyncService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;

class CourseChapterTest extends BaseTestCase
{
    public function testSyncWhenCreate()
    {
        $copyCourses = array(
            array('id' => 1),
            array('id' => 2),
            array('id' => 3),
        );

        $chapter = $this->getCourseChapterDao()->create(
            array('courseId' => 1,
                'number' => 1,
                'seq' => 1,
                'title' => '123',
            ));

        $this->mockBiz('Course:CourseDao', array(
           array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => $copyCourses)
        ));
        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_CREATE, $chapter['id']);

        $chapters = $this->getCourseChapterDao()->findByCopyId($chapter['id']);
        $this->assertCount(3, $chapters);
        $this->assertEquals(array(1, 2, 3), array_column($chapters, 'courseId'));
    }

    /**
     * @return CourseChapterDao
     */
    private function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    /**
     * @return SyncService
     */
    private function getSyncService()
    {
        return $this->createService('Sync:SyncService');
    }
}
