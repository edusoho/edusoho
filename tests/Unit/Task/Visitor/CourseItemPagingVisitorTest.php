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

        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array(
            'direction' => 'down',
            'limit' => 1,
            'offsetSeq' => 1,
            'offsetTaskId' => 1,
        ));

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
            ));
        }
        $result = $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));

        $this->assertEquals(1, count($result[0]));
        $this->assertEquals(1, $result[1]);
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
        $visitor = new CourseItemPagingVisitor($this->getBiz(), $courseId, array(
            'direction' => 'up',
            'limit' => 1,
            'offsetSeq' => 1,
        ));

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
            ));
        }

        $result = $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));
        $this->assertEquals(1, count($result[0]));
        $this->assertEquals(1, $result[1]);
        $this->assertEquals(1, $result[0][0]['id']);
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
