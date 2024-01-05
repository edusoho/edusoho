<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\LatestStatusesDataTag;
use Biz\BaseTestCase;

class LatestStatusesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new LatestStatusesDataTag();
        $datas = $datatag->getData(['count' => 5]);
        $this->assertEmpty($datas);

        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'findUsersByIds',
                'returnValue' => [1 => ['id' => 1, 'nickname' => 'user1 nickname'], 2 => ['id' => 2, 'nickname' => 'user2 nickname'], 3 => ['id' => 3, 'nickname' => 'user1 nickname']],
            ],
        ]);

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'findCoursesByCourseSetId',
                'returnValue' => [['id' => 1], ['id' => 2]],
            ],
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'courseSetId' => 1],
            ],
            [
                'functionName' => 'searchCourses',
                'returnValue' => [['id' => 1, 'canLearn' => 1]],
            ],
        ]);

        $this->getStatusService()->publishStatus(['userId' => 1, 'courseId' => 1, 'type' => 'become_student', 'objectType' => 'course', 'objectId' => 1, 'properties' => ['course' => ['id' => 1]]]);
        $this->getStatusService()->publishStatus(['userId' => 2, 'courseId' => 1, 'type' => 'become_student', 'objectType' => 'course', 'objectId' => 2, 'properties' => ['course' => ['id' => 1]]]);
        $this->getStatusService()->publishStatus(['userId' => 1, 'classroomId' => 1, 'type' => 'become_student', 'objectType' => 'classroom', 'objectId' => 1, 'properties' => ['classroom' => ['id' => 1]]]);
        $this->getStatusService()->publishStatus(['userId' => 2, 'classroomId' => 1, 'type' => 'become_student', 'objectType' => 'classroom', 'objectId' => 2, 'properties' => ['classroom' => ['id' => 1]]]);
        $this->getStatusService()->publishStatus(['userId' => 1, 'courseId' => 3, 'type' => 'become_student', 'objectType' => 'course', 'objectId' => 3, 'private' => 1, 'properties' => ['course' => ['id' => 1]]]);

        $datas = $datatag->getData(['count' => 5, 'objectType' => 'classroom', 'objectId' => 1, 'mode' => 'simple']);
        $this->assertEquals(2, count($datas));

        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'findActiveCoursesByClassroomId',
                'returnValue' => [['id' => 1], ['id' => 2]],
            ],
        ]);
        $datas = $datatag->getData(['count' => 5, 'objectType' => 'classroom', 'objectId' => 1, 'mode' => 'simple']);
        $this->assertEquals(4, count($datas));

        $datas = $datatag->getData(['count' => 5, 'objectType' => 'courseSet', 'objectId' => 1, 'mode' => 'simple']);
        $this->assertEquals(2, count($datas));

        $datas = $datatag->getData(['count' => 5, 'objectType' => 'course', 'objectId' => 1, 'mode' => 'simple']);
        $this->assertEquals(2, count($datas));

        $datas = $datatag->getData(['count' => 5, 'objectType' => 'course', 'objectId' => 1, 'mode' => 'simple', 'private' => 0]);
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
