<?php

namespace Tests\Unit\Task;

use Biz\BaseTestCase;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;

class SortCourseItemVisitorTest extends BaseTestCase
{
    public function testVisitDefaultStrategy()
    {
        $sortIds = array(
            'chapter-1',
            'chapter-2',
            'chapter-3',
            'lesson-1',
            'lesson-2',
            'lesson-3',
            'chapter-4',
        );

        $courseId = 1;
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse',  'returnValue' => array('id' => $courseId)),
            array('functionName' => 'getChapter',  'returnValue' => array('id' => 1, 'courseId' => $courseId, 'type' => 'chapter')),
            array('functionName' => 'updateChapter',  'returnValue' => ''),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'findTasksByChapterId',  'returnValue' => array()),
            array('functionName' => 'updateSeq',  'returnValue' => array()),
            array('functionName' => 'getTask',  'returnValue' => array()),
        ));
        $visitor = new \Biz\Task\Visitor\SortCourseItemVisitor($this->getBiz(), $courseId, $sortIds);
        $visitor->visitDefaultStrategy(new DefaultStrategy($this->getBiz()));
    }

    public function testVisitNormalStrategy()
    {
        $sortIds = array(
            'chapter-1',
            'chapter-2',
            'chapter-3',
            'task-1',
            'task-2',
            'task-3',
            'chapter-4',
        );

        $courseId = 1;
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse',  'returnValue' => array('id' => $courseId)),
            array('functionName' => 'getChapter',  'returnValue' => array('id' => 1, 'courseId' => $courseId, 'type' => 'chapter')),
            array('functionName' => 'updateChapter',  'returnValue' => ''),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'findTasksByChapterId',  'returnValue' => array()),
            array('functionName' => 'updateSeq',  'returnValue' => array()),
            array('functionName' => 'getTask',  'returnValue' => array('id' => 1, 'isOptional' => 0)),
        ));
        $visitor = new \Biz\Task\Visitor\SortCourseItemVisitor($this->getBiz(), $courseId, $sortIds);
        $visitor->visitNormalStrategy(new NormalStrategy($this->getBiz()));
    }
}
