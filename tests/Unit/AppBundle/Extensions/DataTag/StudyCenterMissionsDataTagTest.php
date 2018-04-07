<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\Announcement\Service\AnnouncementService;
use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\StudyCenterMissionsDataTag;

class StudyCenterMissionsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDataWithInvalidArguments()
    {
        $dataTag = new StudyCenterMissionsDataTag();
        $dataTag->getData(array('userId' => 123));
    }

    public function testGetDataWithoutCourseMember()
    {
        $dataTag = new StudyCenterMissionsDataTag();

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'searchMembers',
                    'withParams' => array(
                        array('userId' => 123, 'role' => 'student'),
                        array('classroomId' => 'DESC', 'createdTime' => 'DESC'),
                        0,
                        PHP_INT_MAX,
                    ),
                    'returnValue' => array(),
                ),
            )
        );
        $result = $dataTag->getData(array('userId' => 123, 'count' => 3, 'missionCount' => 5));
        $this->assertEmpty($result);
    }

    /**
     * @return AnnouncementService
     */
    private function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement:AnnouncementService');
    }
}
