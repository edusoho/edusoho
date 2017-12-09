<?php

namespace Tests\Unit\Xapi;

use Biz\BaseTestCase;

class AskQuestionTypeTest extends BaseTestCase
{
    public function testPackage()
    {
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'withParams' => array(0, 121),
                    'returnValues' => array(
                        'id' => 222,
                        'courseId' => 2221,
                        'courseSetId' => 2222,
                        'type' => 'question',
                        'taskId' => 2223,
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'withParams' => array(2223),
                    'returnValues' => array(
                        'type' => 'video',
                        'activityId' => 2224,
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(2221),
                    'returnValues' => array(
                        'title' => 'course title',
                    ),
                ),
            )
        );
    }
}
