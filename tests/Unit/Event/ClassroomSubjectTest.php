<?php

namespace Tests\Unit\File;

use Biz\BaseTestCase;
use Biz\Event\Service\Impl\ClassroomSubject;

class ClassroomSubjectTest extends BaseTestCase
{
    public function testGetSubject()
    {
        $subject = new ClassroomSubject($this->biz);
        $classroomService = $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroom',
                    'withParams' => array(123),
                    'returnValue' => array('title' => 'classroom_title'),
                ),
            )
        );
        $result = $subject->getSubject(123);

        $this->assertEquals('classroom_title', $result['title']);
        $classroomService->shouldHaveReceived('getClassroom');
    }
}
