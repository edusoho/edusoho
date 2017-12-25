<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ClassroomMissionsDataTag;

class ClassroomMissionsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'searchMembers',
                    'returnValue' => array(
                        array(
                            'classroomId' => '1',
                            'userId' => '1',
                        ),
                        array(
                            'classroomId' => '2',
                            'userId' => '1',
                        ),
                        array(
                            'classroomId' => '3',
                            'userId' => '1',
                        ),
                    ),
                    'withParams' => array(
                        array(
                            'userId' => 1,
                            'locked' => 0,
                            'role' => 'student',
                        ),
                        array('createdTime' => 'DESC'),
                        0,
                        3,
                    ),
                ),
                array(
                    'functionName' => 'findClassroomsByIds',
                    'returnValue' => array(
                        array(
                            'id' => '1',
                        ),
                        array(
                            'id' => '2',
                        ),
                        array(
                            'id' => '3',
                        ),
                    ),
                    'withParams' => array(array(1, 2, 3)),
                ),
                array(
                    'functionName' => 'findActiveCoursesByClassroomId',
                    'returnValue' => array(
                        array(
                            'id' => '11',
                            'taskNum' => '1',
                        ),
                        array(
                            'id' => '12',
                            'taskNum' => '2',
                        ),
                        array(
                            'id' => '13',
                            'taskNum' => '1',
                        ),
                    ),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'findActiveCoursesByClassroomId',
                    'returnValue' => array(
                        array(
                            'id' => '14',
                            'taskNum' => '3',
                        ),
                    ),
                    'withParams' => array(2),
                ),
                array(
                    'functionName' => 'findActiveCoursesByClassroomId',
                    'returnValue' => array(),
                    'withParams' => array(3),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskResultService',
            array(
                array(
                    'functionName' => 'countTaskResults',
                    'returnValue' => '3',
                    'withParams' => array(
                        array(
                            'userId' => 1,
                            'courseIds' => array(11, 12, 13),
                        ),
                    ),
                ),
                array(
                    'functionName' => 'countTaskResults',
                    'returnValue' => '1',
                    'withParams' => array(
                        array(
                            'userId' => 1,
                            'courseIds' => array(14),
                        ),
                    ),
                ),
                array(
                    'functionName' => 'searchTaskResults',
                    'returnValue' => array(
                        array(
                            'id' => '1',
                            'status' => 'finish',
                            'courseTaskId' => '11',
                        ),
                        array(
                            'id' => '2',
                            'status' => 'start',
                            'courseTaskId' => '12',
                        ),
                        array(
                            'id' => '3',
                            'status' => 'finish',
                            'courseTaskId' => '13',
                        ),
                    ),
                    'withParams' => array(
                        array(
                            'userId' => 1,
                            'courseIds' => array(11, 12, 13),
                        ),
                        array('finishedTime' => 'ASC'),
                        0,
                        3,
                    ),
                ),
                array(
                    'functionName' => 'searchTaskResults',
                    'returnValue' => array(
                        array(
                            'id' => '4',
                            'status' => 'finish',
                            'courseTaskId' => '14',
                        ),
                    ),
                    'withParams' => array(
                        array(
                            'userId' => 1,
                            'courseIds' => array(14),
                        ),
                        array('finishedTime' => 'ASC'),
                        0,
                        1,
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'searchTasks',
                    'returnValue' => array(
                        array(
                            'id' => '21',
                        ),
                        array(
                            'id' => '22',
                        ),
                    ),
                    'withParams' => array(
                        array(
                            'status' => 'published',
                            'courseIds' => array(11, 12, 13),
                            'excludeIds' => array(11, 13), //taskId
                        ),
                        array('seq' => 'ASC'),
                        0,
                        2,
                    ),
                ),
                array(
                    'functionName' => 'searchTasks',
                    'returnValue' => array(
                        array(
                            'id' => '25',
                        ),
                        array(
                            'id' => '26',
                        ),
                    ),
                    'withParams' => array(
                        array(
                            'status' => 'published',
                            'courseIds' => array(14),
                            'excludeIds' => array(14),
                        ),
                        array('seq' => 'ASC'),
                        0,
                        2,
                    ),
                ),
                array(
                    'functionName' => 'canLearnTask',
                    'returnValue' => array(true),
                ),
            )
        );

        $arguments = array(
            'userId' => '1',
            'count' => '3',
            'missionCount' => '2',
        );

        $datatag = new ClassroomMissionsDataTag();
        $studyMission = $datatag->getData($arguments);
        $studyMission1 = array(
            'tasks' => array(
                array('id' => $studyMission[0]['tasks'][0]['id']),
                array('id' => $studyMission[0]['tasks'][1]['id']),
            ),
            'learnedTaskNum' => $studyMission[0]['learnedTaskNum'],
            'allTaskNum' => $studyMission[0]['allTaskNum'],
        );
        $arr1 = array(
            'tasks' => array(
                array('id' => '21'),
                array('id' => '22'),
            ),
            'learnedTaskNum' => '2',
            'allTaskNum' => '4',
        );
        $this->assertEquals(2, count($studyMission));
        $this->assertArrayEquals($arr1, $studyMission1);
    }
}
