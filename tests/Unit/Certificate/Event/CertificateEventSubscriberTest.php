<?php

namespace Tests\Unit\Certificate\Event;

use Biz\BaseTestCase;
use Biz\Certificate\Event\CertificateEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class CertificateEventSubscriberTest extends BaseTestCase
{
    public function testOnCourseTaskFinishWhenFinishCourseTask()
    {
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'getCourseSet',
                'returnValue' => ['id' => 1],
            ],
        ]);
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'compulsoryTaskNum' => 1, 'courseSetId' => 1],
            ],
        ]);

        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'searchMembers',
                'returnValue' => [['id' => 1, 'finishedTime' => time(), 'learnedCompulsoryTaskNum' => 1]],
            ],
        ]);

        $this->mockBiz('Certificate:CertificateService', [
            [
                'functionName' => 'findByTargetIdAndTargetType',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $recordService = $this->mockBiz('Certificate:RecordService', [
            [
                'functionName' => 'autoIssueCertificates',
            ],
        ]);

        $subscriber = new CertificateEventSubscriber($this->biz);
        $event = new Event(
            [
                'courseId' => 1,
                'userId' => 1,
            ]
        );

        $subscriber->onCourseTaskFinish($event);

        $recordService->shouldHaveReceived('autoIssueCertificates')->times(1);
    }

    public function testOnCourseTaskFinishWhenFinishClassroomTask()
    {
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'getCourseSet',
                'returnValue' => ['id' => 1],
            ],
        ]);
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'compulsoryTaskNum' => 1, 'courseSetId' => 1],
            ],
        ]);

        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'findClassroomIdsByCourseId',
                'returnValue' => [['id' => 1, 'classroomId' => 1, 'courseId' => 1]],
            ],
            [
                'functionName' => 'findCoursesByClassroomId',
                'returnValue' => [['id' => 1, 'classroomId' => 1, 'userId' => 1]],
            ],
        ]);

        $this->mockBiz('Certificate:CertificateService', [
            [
                'functionName' => 'findByTargetIdAndTargetType',
                'returnValue' => [['id' => 1, 'targetId' => 1]],
            ],
        ]);

        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'countMembers',
                'returnValue' => 1,
            ],
            [
                'functionName' => 'searchMembers',
                'returnValue' => [['id' => 1, 'finishedTime' => 0, 'learnedCompulsoryTaskNum' => 0]],
            ],
        ]);

        $recordService = $this->mockBiz('Certificate:RecordService', [
            [
                'functionName' => 'autoIssueCertificates',
            ],
        ]);

        $subscriber = new CertificateEventSubscriber($this->biz);
        $event = new Event(
            [
                'courseId' => 1,
                'userId' => 1,
            ]
        );
        $subscriber->onCourseTaskFinish($event);
        $recordService->shouldHaveReceived('autoIssueCertificates')->times(2);
    }
}
