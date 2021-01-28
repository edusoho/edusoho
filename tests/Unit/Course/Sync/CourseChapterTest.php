<?php

namespace Tests\Unit\Course\Sync;

use Biz\BaseTestCase;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Sync\Service\AbstractSychronizer;
use Biz\Sync\Service\SyncService;

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
           array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => $copyCourses),
        ));
        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_CREATE, $chapter['id']);

        $chapters = $this->getCourseChapterDao()->findByCopyId($chapter['id']);
        $this->assertCount(3, $chapters);
        $this->assertEquals(array(1, 2, 3), array_column($chapters, 'courseId'));
    }

    public function testSyncWhenUpdate()
    {
        //创建
        $copyCourses = array(
            array('id' => 1),
            array('id' => 2),
            array('id' => 3),
        );

        $chapter = $this->getCourseChapterDao()->create(array(
            'courseId' => 1,
            'number' => 1,
            'seq' => 1,
            'title' => '123',
        ));

        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => $copyCourses),
        ));
        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_CREATE, $chapter['id']);

        //更新
        $chapter = $this->getCourseChapterDao()->update($chapter['id'], array(
            'number' => 2,
            'seq' => 3,
        ));

        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_UPDATE, $chapter['id']);

        $chapters = $this->getCourseChapterDao()->findByCopyId($chapter['id']);
        $this->assertCount(3, $chapters);
        $this->assertEquals(array(2, 2, 2), array_column($chapters, 'number'));
        $this->assertEquals(array(3, 3, 3), array_column($chapters, 'seq'));
    }

    public function testSyncWhenDelete()
    {
        //创建
        $copyCourses = array(
            array('id' => 1),
            array('id' => 2),
            array('id' => 3),
        );

        $chapter = $this->getCourseChapterDao()->create(array(
            'courseId' => 1,
            'number' => 1,
            'seq' => 1,
            'title' => '123',
        ));

        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => $copyCourses),
        ));
        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_CREATE, $chapter['id']);

        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_DELETE, $chapter['id']);

        $chapters = $this->getCourseChapterDao()->findByCopyId($chapter['id']);
        $this->assertCount(0, $chapters);
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
