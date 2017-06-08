<?php

namespace Biz\Task\Visitor;

use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class SortCourseItemVisitor implements CourseStrategyVisitorInterface
{
    private $biz;

    private $courseId;

    /**
     * chapter-1 / task-3
     *
     * @var string
     */
    private $itemIds;

    public function __construct(Biz $biz, $courseId, $itemIds)
    {
        $this->biz = $biz;
        $this->courseId = $courseId;
        $this->itemIds = $itemIds;
    }

    public function visitDefaultStrategy(DefaultStrategy $defaultStrategy)
    {
        $chapterNumber = 1;
        $unitNumber = 1;
        $needResetUnitNumber = false;
        $seq = 1;
        $taskNumber = 1;
        foreach ($this->itemIds as $itemId) {
            list($type, $chapterId) = explode('-', $itemId);

            $chapter = $this->getCourseService()->getChapter($this->courseId, $chapterId);
            switch ($chapter['type']) {
                case 'chapter':
                case 'unit':
                    $this->updateChapterSeq($chapter, $seq, $chapterNumber, $unitNumber, $needResetUnitNumber);
                    break;
                case 'lesson':
                    $fields['seq'] = $seq;
                    ++$seq;
                    $fields['number'] = $this->updateTaskSeq($chapterId, $taskNumber, $seq);
                    $this->getCourseService()->updateChapter($this->courseId, $chapterId, $fields);
                    break;
                default:
                    throw new InvalidArgumentException();
            }
        }
    }

    /**
     * 返回chapter的number，可用于判断是否全是选修
     *
     * @param $chapter
     * @param $taskNumber
     * #return string
     */
    private function updateTaskSeq($chapterId, &$taskNumber, &$seq)
    {
        $tasks = $this->getTaskService()->findTasksByChapterId($chapterId);

        $normalTaskCount = 0;
        foreach ($tasks as $task) {
            if ($task['isOptional'] == 0) {
                ++$normalTaskCount;
            }
        }

        $taskSeqMap = array('preparation' => 1, 'lesson' => 2, 'exercise' => 3, 'homework' => 4, 'extraClass' => 5);
        //新增加的task seq不正确，重新排序
        uasort($tasks, function ($task1, $task2) use ($taskSeqMap) {
            $seq1 = $taskSeqMap[$task1['mode']];
            $seq2 = $taskSeqMap[$task2['mode']];

            return $seq1 > $seq2;
        });

        $subTaskNumber = 1;
        foreach ($tasks as $task) {
            $fields = array(
                'seq' => $seq,
                'number' => $this->getTaskNumber($taskNumber, $task, $normalTaskCount, $subTaskNumber),
            );

            $this->getTaskService()->updateSeq($task['id'], $fields);
            ++$seq;
        }

        $chapterNumber = 0;
        if ($normalTaskCount) {
            $chapterNumber = $taskNumber;
            ++$taskNumber;
        }

        return $chapterNumber;
    }

    private function getTaskNumber($taskNumber, $task, $normalTaskCount, &$subTaskNumber)
    {
        if ($task['isOptional']) {
            return '';
        } else {
            if ($normalTaskCount == 1) {
                return $taskNumber;
            } else {
                return $taskNumber.'-'.$subTaskNumber++;
            }
        }
    }

    public function visitNormalStrategy(NormalStrategy $normalStrategy)
    {
        $chapterNumber = 1;
        $unitNumber = 1;
        $needResetUnitNumber = false;
        $seq = 1;
        $taskNumber = 1;
        foreach ($this->itemIds as $itemId) {
            list($type, $chapterIdOrTaskId) = explode('-', $itemId);

            switch ($type) {
                case 'chapter':
                    $chapter = $this->getCourseService()->getChapter($this->courseId, $chapterIdOrTaskId);
                    $this->updateChapterSeq($chapter, $seq, $chapterNumber, $unitNumber, $needResetUnitNumber);

                    break;
                case 'task':
                    $task = $this->getTaskService()->getTask($chapterIdOrTaskId);
                    if ($task['isOptional']) {
                        $number = '';
                    } else {
                        $number = $taskNumber;
                        ++$taskNumber;
                    }

                    $this->getTaskService()->updateSeq(
                        $chapterIdOrTaskId,
                        array(
                            'seq' => $seq,
                            'number' => $number,
                        )
                    );

                    ++$seq;
                    break;
                default:
                    throw new InvalidArgumentException();
            }
        }
    }

    private function updateChapterSeq($chapter, &$seq, &$chapterNumber, &$unitNumber, &$needResetUnitNumber)
    {
        $fields = array(
            'seq' => $seq,
        );

        if ($needResetUnitNumber) {
            $unitNumber = 1;
            $needResetUnitNumber = false;
        }

        if ($chapter['type'] == 'chapter') {
            $fields['number'] = $chapterNumber;
            ++$chapterNumber;
            $needResetUnitNumber = true;
            ++$seq;
        }

        if ($chapter['type'] == 'unit') {
            ++$seq;
            $fields['number'] = $unitNumber;
            ++$unitNumber;
        }

        $this->getCourseService()->updateChapter($this->courseId, $chapter['id'], $fields);
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
