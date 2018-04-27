<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\StudyCenterMissionsDataTag;

class StudyCenterMissionsDataTagTest extends BaseTestCase
{
    public function testGetDataMissParam()
    {
        //error path
        try {
            $message = '';
            $arguments = array(
                'userId' => 1,
                'count' => 10,
            );
            $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
            $studyCenterMissionsDataTag->getData($arguments);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('参数缺失', $message);
    }

    public function testGetDataEmptyMember()
    {
        $arguments = array(
            'userId' => 1,
            'count' => 3,
            'missionCount' => 2,
        );
        $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
        $result = $studyCenterMissionsDataTag->getData($arguments);
        $this->assertTrue(empty($result));
    }

    public function testGetDataOnlyCourse()
    {
        $arguments = array(
            'userId' => 1,
            'count' => 5,
            'missionCount' => 10,
        );

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'searchMembers', 'returnValue' => array(
                array('userId' => 1, 'courseId' => 1),
                array('userId' => 1, 'courseId' => 2),
            )),
        ));

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'findCoursesByIds', 'returnValue' => array(
                array('id' => 1, 'parentId' => 0, 'taskNum' => 2),
                array('id' => 2, 'parentId' => 0, 'taskNum' => 2),
            )),
        ));

        //task为空时
        $this->mockBiz('Task:TaskService',
            array(
                array('functionName' => 'findToLearnTasksByCourseIdForMission', 'returnValue' => array()),
            )
        );
        $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
        $result = $studyCenterMissionsDataTag->getData($arguments);
        $this->assertArrayEquals(array('courses' => array(), 'classrooms' => array()), $result);

        //course['task']和finished的数量相同时
        $this->mockBiz('Task:TaskService',
            array(
                array('functionName' => 'findToLearnTasksByCourseIdForMission', 'returnValue' => array(
                    array('id' => 1, 'title' => 'task1'),
                )),
            )
        );
        $this->mockBiz('Task:TaskResultService',
            array(
                array('functionName' => 'countTaskResults', 'returnValue' => 2),
            )
        );
        $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
        $result = $studyCenterMissionsDataTag->getData($arguments);
        $this->assertArrayEquals(array('courses' => array(), 'classrooms' => array()), $result);

        //happy path
        $this->mockBiz('Task:TaskResultService',
            array(
                array('functionName' => 'countTaskResults', 'returnValue' => 1),
            )
        );
        $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
        $result = $studyCenterMissionsDataTag->getData($arguments);
        $assertArray = array(
            'courses' => array(
                array(
                    'id' => 1,
                    'parentId' => 0,
                    'taskNum' => 2,
                    'finishTaskNum' => 1,
                    'tasks' => array(
                        array('id' => 1, 'title' => 'task1'),
                    ),
                ),
                array(
                    'id' => 2,
                    'parentId' => 0,
                    'taskNum' => 2,
                    'finishTaskNum' => 1,
                    'tasks' => array(
                        array('id' => 1, 'title' => 'task1'),
                    ),
                ),
            ),
            'classrooms' => array(),
        );
        $this->assertArrayEquals($assertArray, $result);
    }

    public function testGetDataOnlyClassroom()
    {
        $arguments = array(
            'userId' => 1,
            'count' => 5,
            'missionCount' => 10,
        );

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'searchMembers', 'returnValue' => array(
                array('userId' => 1, 'courseId' => 1),
                array('userId' => 1, 'courseId' => 2),
            )),
        ));

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'findCoursesByIds', 'returnValue' => array(
                array('id' => 1, 'parentId' => 3, 'taskNum' => 2),
                array('id' => 2, 'parentId' => 3, 'taskNum' => 2),
            )),
        ));

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'findClassroomsByCoursesIds', 'returnValue' => array(
                array('courseId' => 1, 'classroomId' => 1),
                array('courseId' => 2, 'classroomId' => 2),
            )),
            array('functionName' => 'findClassroomsByIds', 'returnValue' => array(
                1 => array('id' => 1, 'title' => 'classroom1'),
                2 => array('id' => 2, 'title' => 'classroom2'),
            )),
        ));
        // finishedTasks为空
        // tasks为空时
        $this->mockBiz('Task:TaskService',
            array(
                array('functionName' => 'findTasksByCourseId', 'returnValue' => array()),
            )
        );
        $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
        $result = $studyCenterMissionsDataTag->getData($arguments);
        $this->assertArrayEquals(array('courses' => array(), 'classrooms' => array()), $result);

        //task不为空
        $this->mockBiz('Task:TaskService',
            array(
                array('functionName' => 'findTasksByCourseId', 'returnValue' => array(
                    array('id' => 1, 'title' => 'task1'),
                )),
            )
        );
        $studyCenterMissionsDataTag = new StudyCenterMissionsDataTag();
        $result = $studyCenterMissionsDataTag->getData($arguments);
        $this->assertArrayEquals(array(
            'courses' => array(),
            'classrooms' => array(
                'class-1' => array(
                    'id' => 1,
                    'title' => 'classroom1',
                    'tasks' => array(
                        array('id' => 1, 'title' => 'task1', 'result' => array()),
                    ),
                    'allTaskNum' => 2,
                    'learnedTaskNum' => 0,
                ),
                'class-2' => array(
                    'id' => 2,
                    'title' => 'classroom2',
                    'tasks' => array(
                        array('id' => 1, 'title' => 'task1', 'result' => array()),
                    ),
                    'allTaskNum' => 2,
                    'learnedTaskNum' => 0,
                ),
            ),
        ), $result);

        // finishedTasks不为空
        $this->mockBiz('Task:TaskService',
            array(
                array('functionName' => 'searchTasks', 'returnValue' => array(
                    array('id' => 1, 'title' => 'task1'),
                )),
            )
        );
        $this->assertArrayEquals(array(
            'courses' => array(),
            'classrooms' => array(
                'class-1' => array(
                    'id' => 1,
                    'title' => 'classroom1',
                    'tasks' => array(
                        array('id' => 1, 'title' => 'task1', 'result' => array()),
                    ),
                    'allTaskNum' => 2,
                    'learnedTaskNum' => 0,
                ),
                'class-2' => array(
                    'id' => 2,
                    'title' => 'classroom2',
                    'tasks' => array(
                        array('id' => 1, 'title' => 'task1', 'result' => array()),
                    ),
                    'allTaskNum' => 2,
                    'learnedTaskNum' => 0,
                ),
            ),
        ), $result);
    }
}
