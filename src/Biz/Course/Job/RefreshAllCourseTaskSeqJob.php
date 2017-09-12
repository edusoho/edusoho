<?php

namespace Biz\Course\Job;

use Biz\Task\Visitor\CourseItemSortingVisitor;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshAllCourseTaskSeqJob extends AbstractJob
{
    public function execute()
    {
        $sql = 'SELECT id,courseType FROM `course_v8` WHERE parentId = 0 ORDER BY id';
        $allCourses = $this->biz['db']->fetchAll($sql);

        foreach ($allCourses as $course) {
            $this->refreshCourseTaskSeq($course);
        }
    }

    private function refreshCourseTaskSeq($course)
    {
        if ($course['courseType'] == 'default') {
            $this->refreshDefaultCourseTaskSeq($course);
        } else {
            $this->refreshNormalCourseTaskSeq($course);
        }
    }

    private function refreshDefaultCourseTaskSeq($course)
    {
        $sql = "SELECT * FROM `course_chapter` WHERE courseId = {$course['id']} ORDER BY seq ASC";
        $chapters = $this->biz['db']->fetchAll($sql);

        $seqArr = array();
        foreach ($chapters as $chapter) {
            $seqArr[] = 'chapter-'.$chapter['id'];
        }

        if (empty($seqArr)) {
            return;
        }

        $this->createCourseStrategy($course)->accept(new CourseItemSortingVisitor($this->biz, $course['id'], $seqArr));
    }

    private function refreshNormalCourseTaskSeq($course)
    {
        $sql = "SELECT * FROM `course_chapter` WHERE courseId = {$course['id']}";
        $chapters = $this->biz['db']->fetchAll($sql);
        $sql = "SELECT * FROM `course_task` WHERE courseId = {$course['id']}";
        $tasks = $this->biz['db']->fetchAll($sql);

        $items = array_merge($chapters, $tasks);
        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        $seqArr = array();
        foreach ($items as $item) {
            if ($item['type'] == 'chapter' || $item['type'] == 'unit') {
                $seqArr[] = 'chapter-'.$item['id'];
            } elseif ($item['type'] != 'lesson') {
                $seqArr[] = 'task-'.$item['id'];
            }
        }

        if (empty($seqArr)) {
            return;
        }

        $this->createCourseStrategy($course)->accept(new CourseItemSortingVisitor($this->biz, $course['id'], $seqArr));
    }

    protected function createCourseStrategy($course)
    {
        return $this->biz['course.strategy_context']->createStrategy($course['courseType']);
    }
}
