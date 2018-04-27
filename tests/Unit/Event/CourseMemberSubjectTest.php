<?php

namespace Tests\Unit\Event;

use Biz\BaseTestCase;
use Biz\Event\Service\Impl\CourseMemberSubject;

class CourseMemberSubjectTest extends BaseTestCase
{
    public function testGetSubject()
    {
        $subject = new CourseMemberSubject($this->biz);
        $result = $subject->getSubject(0);
        $this->assertNull($result);

        $user = $this->getCurrentUser();
        $mockResult = array('id' => 1, 'courseId' => 1, 'userId' => $user['id']);
        $courseMemberService = $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => $mockResult,
            ),
        ));
        $result = $subject->getSubject(123);

        $this->assertArrayEquals($mockResult, $result);
        $courseMemberService->shouldHaveReceived('getCourseMember');
    }
}
