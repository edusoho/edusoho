<?php

namespace Tests\Unit\Event;

use Biz\BaseTestCase;
use Biz\Event\Service\Impl\CourseSubject;

class CourseSubjectTest extends BaseTestCase
{
    public function testGetSubject()
    {
        $subject = new CourseSubject($this->biz);
        $result = $subject->getSubject(0);
        $this->assertNull($result);

        $user = $this->getCurrentUser();
        $mockResult = array('id' => 1, 'title' => 'course title');
        $courseMemberService = $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => $mockResult,
            ),
        ));
        $result = $subject->getSubject(123);

        $this->assertArrayEquals($mockResult, $result);
        $courseMemberService->shouldHaveReceived('getCourse');
    }
}
