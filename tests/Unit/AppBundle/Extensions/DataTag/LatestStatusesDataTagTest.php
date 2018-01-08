<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestStatusesDataTag;

class LatestStatusesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new LatestStatusesDataTag();
        $datas = $datatag->getData(array('count' => 5));
        $this->assertEmpty($datas); 

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'returnValue' => array(1 => array('id'=>1, 'nickname' => 'user1 nickname'), 2 => array('id'=>2, 'nickname' => 'user2 nickname'), 3 => array('id'=>3, 'nickname' => 'user1 nickname'))
            )
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'findCoursesByCourseSetId',
                'returnValue' => array(array('id' => 1), array('id' => 2))
            ),
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'courseSetId' => 1)
            )
        ));
        
        $status1 = $this->getStatusService()->publishStatus(array('userId' => 1, 'courseId' => 1, 'type' => 'become_student', 'objectType' => 'course', 'objectId' => 1, 'properties' => array('course' => array('id' => 1))));
        $status2 = $this->getStatusService()->publishStatus(array('userId' => 2, 'courseId' => 1, 'type' => 'become_student', 'objectType' => 'course', 'objectId' => 2, 'properties' => array('course' => array('id' => 1))));
        $status3 = $this->getStatusService()->publishStatus(array('userId' => 1, 'classroomId' => 1, 'type' => 'become_student', 'objectType' => 'classroom', 'objectId' => 1, 'properties' => array('classroom' => array('id' => 1))));
        $status4 = $this->getStatusService()->publishStatus(array('userId' => 2, 'classroomId' => 1, 'type' => 'become_student', 'objectType' => 'classroom', 'objectId' => 2, 'properties' => array('classroom' => array('id' => 1))));
        $status5 = $this->getStatusService()->publishStatus(array('userId' => 1, 'courseId' => 3, 'type' => 'become_student', 'objectType' => 'course', 'objectId' => 3, 'private' => 1, 'properties' => array('course' => array('id' => 1))));

        $datas = $datatag->getData(array('count' => 5, 'objectType' => 'classroom', 'objectId' => 1, 'mode' => 'simple'));
        $this->assertEquals(2, count($datas)); 

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'findActiveCoursesByClassroomId',
                'returnValue' => array(array('id'=>1), array('id'=>2))
            )
        ));
        $datas = $datatag->getData(array('count' => 5, 'objectType' => 'classroom', 'objectId' => 1, 'mode' => 'simple'));
        $this->assertEquals(4, count($datas)); 

        $datas = $datatag->getData(array('count' => 5, 'objectType' => 'courseSet', 'objectId' => 1, 'mode' => 'simple'));
        $this->assertEquals(2, count($datas)); 

        $datas = $datatag->getData(array('count' => 5, 'objectType' => 'course', 'objectId' => 1, 'mode' => 'simple'));
        $this->assertEquals(2, count($datas)); 

        $datas = $datatag->getData(array('count' => 5, 'objectType' => 'course', 'objectId' => 1, 'mode' => 'simple', 'private' => 0));
        $this->assertEquals(2, count($datas)); 
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    public function getStatusService()
    {
        return $this->getServiceKernel()->createService('User:StatusService');
    }
}
