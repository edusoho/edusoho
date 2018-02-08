<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseMissionsDataTag;

class CourseMissionsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $dataTag = new CourseMissionsDataTag();
        $dataTag->getData(array('userId' => 1, 'missionCount' => 5));
    }

    public function testEmptyData()
    {
        $dataTag = new CourseMissionsDataTag();
        $data = $dataTag->getData(array('userId' => 1, 'count' => 5, 'missionCount' => 5));

        $this->assertEmpty($data);
    }

    public function testGetData()
    {
        $this->_mockMemberService();
        $this->_mockCourseService();
        $this->_mockTaskResultService();
        $this->_mockTaskService();

        $datatag = new CourseMissionsDataTag();

        $data = $datatag->getData(array('userId' => 1, 'count' => 5, 'missionCount' => 5));

        $this->assertEquals(3, count($data));
        $this->assertEquals(3, count($data[0]['tasks']));
        $this->assertEquals(3, $data[0]['finishTaskNum']);
    }

    private function _mockMemberService()
    {
        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'searchMembers',
                'returnValue' => array(array('courseId' => 3), array('courseId' => 4), array('courseId' => 5), array('courseId' => 6)),
            ),
        ));
    }

    private function _mockCourseService()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'searchCourses',
                'returnValue' => array(array('id' => 3, 'isDefault' => 1), array('id' => 4, 'isDefault' => 0), array('id' => 5, 'isDefault' => 0)),
            ),
        ));
    }

    private function _mockTaskResultService()
    {
        $this->mockBiz('Task:TaskResultService', array(
            array(
                'functionName' => 'countTaskResults',
                'returnValue' => 3,
            ),
        ));
    }

    private function _mockTaskService()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'findToLearnTasksByCourseIdForMission',
                'returnValue' => array(array('id' => 11, 'activity' => array(), 'result' => array()), array('id' => 12, 'activity' => array(), 'result' => array()), array('id' => 20, 'activity' => array(), 'result' => array())),
            ),
            array(
                'functionName' => 'findTasksByCourseId',
                'returnValue' => array(array('id' => 20)),
            ),
        ));
    }
}
