<?php

namespace Tests\Unit\Task;

use Biz\BaseTestCase;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Biz\Task\Visitor\CourseItemPagingVisitor;

class CourseItemPagingVisitorTest extends BaseTestCase
{
    public function testVisitDefaultStrategy()
    {
        $courseId = 1;
        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array());
        $result = $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));
        $this->assertNull($result[1]);
        $this->assertEmpty($result[0]);

        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array());

        for ($i = 0; $i < 5; ++$i) {
            $chapter = $this->getChapterDao()->create(array(
                'courseId' => $courseId,
                'type' => 'lesson',
                'title' => '章节',
                'number' => $i,
                'seq' => $i,
            ));

            $activity = $this->getActivityDao()->create(array('title' => 'activity', 'mediaType' => 'video'));

            $sortIds[] = 'lesson-'.$chapter['id'];
            $this->getTaskDao()->create(array(
                'courseId' => $courseId,
                'title' => 'title',
                'type' => 'text',
                'mode' => 'lesson',
                'categoryId' => $chapter['id'],
                'number' => $i,
                'seq' => $i,
                'createdUserId' => 1,
                'activityId' => $activity['id'],
                'status' => 'published',
            ));
        }
        $result = $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));

        $this->assertEquals(5, count($result[0]));
        $this->assertNull($result[1]);
    }

    public function testVisitNormalStrategy()
    {
        $courseId = 1;
        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array());
        $result = $visitor->visitNormalStrategy(new NormalStrategy($this->getBiz()));
        $this->assertNull($result[1]);
        $this->assertEmpty($result[0]);
    }

    public function testStartPaging()
    {
        $courseId = 1;
        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array());

        for ($i = 0; $i < 5; ++$i) {
            $chapter = $this->getChapterDao()->create(array(
                'courseId' => $courseId,
                'type' => 'lesson',
                'title' => '章节',
                'number' => $i,
                'seq' => $i,
            ));

            $activity = $this->getActivityDao()->create(array('title' => 'activity', 'mediaType' => 'video'));

            $sortIds[] = 'lesson-'.$chapter['id'];
            $this->getTaskDao()->create(array(
                'courseId' => $courseId,
                'title' => 'title',
                'type' => 'text',
                'mode' => 'lesson',
                'categoryId' => $chapter['id'],
                'number' => $i,
                'seq' => $i,
                'createdUserId' => 1,
                'activityId' => $activity['id'],
                'status' => 'published',
            ));
        }

        $result = $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));
        $this->assertEquals(5, count($result[0]));
        $this->assertNull($result[1]);
        $this->assertEquals(1, $result[0][0]['id']);
    }

    public function testStartPagingWithOffsetTaskId()
    {
        $courseId = 1;
        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array('direction' => 'down', 'limit' => 25, 'offsetSeq' => 1, 'offsetTaskId' => 1));

        $chapter = $this->getChapterDao()->create(array(
            'courseId' => $courseId,
            'type' => 'chapter',
            'title' => '章节',
            'number' => 1,
            'seq' => 0,
        ));

        $activity = $this->getActivityDao()->create(array('title' => 'activity', 'mediaType' => 'video'));

        $sortIds[] = 'lesson-'.$chapter['id'];
        $this->getTaskDao()->create(array(
            'courseId' => $courseId,
            'title' => 'title',
            'type' => 'lesson',
            'mode' => 'lesson',
            'categoryId' => $chapter['id'],
            'number' => 0,
            'seq' => 0,
            'createdUserId' => 1,
            'activityId' => $activity['id'],
            'status' => 'published',
        ));

        $result = $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));
        $this->assertEquals(0, count($result[0]));
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    /**
     * @return CourseChapterDao
     */
    private function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    private function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }
}
