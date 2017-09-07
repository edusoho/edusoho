<?php

namespace Tests\Unit\Task;

use Biz\BaseTestCase;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;

class CourseItemSortingVisitorTest extends BaseTestCase
{
    public function testVisitDefaultStrategy()
    {
        $sortIds = array();
        $courseId = 1;

        for ($i = 0; $i < 3; ++$i) {
            $chapter = $this->getChapterDao()->create(array(
                'courseId' => $courseId,
                'type' => 'chapter',
                'title' => '章节',
                'number' => $i,
                'seq' => $i,
            ));

            $sortIds[] = 'chapter-'.$chapter['id'];
        }

        for ($i = 0; $i < 5; ++$i) {
            $chapter = $this->getChapterDao()->create(array(
                'courseId' => $courseId,
                'type' => 'lesson',
                'title' => '章节',
                'number' => $i,
                'seq' => $i,
            ));

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
            ));

            $this->getTaskDao()->create(array(
                'courseId' => $courseId,
                'title' => 'title',
                'type' => 'text',
                'mode' => 'exercise',
                'categoryId' => $chapter['id'],
                'number' => $i,
                'seq' => $i,
                'createdUserId' => 1,
            ));

            $this->getTaskDao()->create(array(
                'courseId' => $courseId,
                'title' => 'title',
                'type' => 'text',
                'mode' => 'preparation',
                'categoryId' => $chapter['id'],
                'number' => $i,
                'seq' => $i,
                'createdUserId' => 1,
            ));
        }

        array_shift($sortIds);

        $visitor = new \Biz\Task\Visitor\CourseItemSortingVisitor($this->getBiz(), $courseId, $sortIds);
        $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));
    }

    public function testVisitNormalStrategy()
    {
        $sortIds = array();
        $courseId = 1;

        for ($i = 0; $i < 3; ++$i) {
            $chapter = $this->getChapterDao()->create(array(
                'courseId' => $courseId,
                'type' => 'chapter',
                'title' => '章节',
                'number' => $i,
                'seq' => $i,
            ));

            $sortIds[] = 'chapter-'.$chapter['id'];
        }

        for ($i = 0; $i < 5; ++$i) {
            $task = $this->getTaskDao()->create(array(
                'courseId' => $courseId,
                'title' => 'title',
                'type' => 'text',
                'mode' => 'lesson',
                'number' => $i,
                'seq' => $i,
                'createdUserId' => 1,
            ));

            $sortIds[] = 'task-'.$task['id'];
        }

        $visitor = new \Biz\Task\Visitor\CourseItemSortingVisitor($this->getBiz(), $courseId, $sortIds);
        $visitor->visitNormalStrategy(new NormalStrategy($this->getBiz()));
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
}
