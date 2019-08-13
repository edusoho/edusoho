<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PromotedTeacherDataTag;

class PromotedTeacherDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $dataTag = new PromotedTeacherDataTag();

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findLatestPromotedTeacher',
                'returnValue' => array(array('id' => 1)),
            ),
            array(
                'functionName' => 'getUserProfile',
                'returnValue' => array('id' => 1, 'mobile' => '15205050505'),
            ),
        ));

        $teacher = $dataTag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertEquals(1, $teacher['id']);
        $this->assertEquals('15205050505', $teacher['mobile']);
    }
}
