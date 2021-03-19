<?php

namespace Test\Unit\Classroom;

use Biz\BaseTestCase;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Classroom\Event\ClassroomThreadEventProcessor;
use Codeages\Biz\Framework\Event\Event;

class ClassroomThreadEventProcessorTest extends BaseTestCase
{
    public function testOnThreadCreate()
    {
        $classroom = $this->createClassroom([]);
        $member = $this->createClassroomMember(['userId' => $this->getCurrentUser()->getId(), 'classroomId' => $classroom['id']]);
        $classroomCourse = $this->createClassroomCourse(['classroomId' => $classroom['id'], 'courseId' => 1]);

        $thread = [
            'targetId' => $classroom['id'],
            'userId' => $member['userId'],
        ];
        $this->mockBiz('Course:ThreadService', [
            [
                'functionName' => 'countThreads',
                'withParams' => [['courseIds' => [$classroomCourse['courseId']], 'userId' => $member['userId'], 'type' => 'discussion']],
                'returnValue' => 1,
            ],
            [
                'functionName' => 'countThreads',
                'withParams' => [['courseIds' => [$classroomCourse['courseId']], 'userId' => $member['userId'], 'type' => 'question']],
                'returnValue' => 2,
            ],
        ]);

        $this->mockBiz('Thread:ThreadService', [
            [
                'functionName' => 'searchThreadCount',
                'withParams' => [['targetType' => 'classroom', 'targetId' => $classroom['id'], 'userId' => $member['userId'], 'type' => 'discussion']],
                'returnValue' => 1,
            ],
            [
                'functionName' => 'searchThreadCount',
                'withParams' => [['targetType' => 'classroom', 'targetId' => $classroom['id'], 'userId' => $member['userId'], 'type' => 'question']],
                'returnValue' => 2,
            ],
        ]);

        $processor = new ClassroomThreadEventProcessor($this->getBiz());
        $processor->onThreadCreate(new Event($thread));

        $memberResult = $this->getClassroomMemberDao()->get($member['id']);
        $classroomResult = $this->getClassroomDao()->get($classroom['id']);

        $this->assertEquals('0', $classroom['threadNum']);
        $this->assertEquals('0', $member['threadNum']);
        $this->assertEquals('0', $member['questionNum']);
        $this->assertEquals('1', $classroomResult['threadNum']);
        $this->assertEquals('2', $memberResult['threadNum']);
        $this->assertEquals('4', $memberResult['questionNum']);
    }

    public function testOnThreadDelete()
    {
        $classroom = $this->createClassroom(['threadNum' => 5, 'postNum' => 10]);
        $member = $this->createClassroomMember(['userId' => $this->getCurrentUser()->getId(), 'classroomId' => $classroom['id'], 'threadNum' => 5, 'questionNum' => 10]);
        $classroomCourse = $this->createClassroomCourse(['classroomId' => $classroom['id'], 'courseId' => 1]);

        $thread = [
            'targetId' => $classroom['id'],
            'postNum' => '5',
            'userId' => $member['userId'],
        ];

        $this->mockBiz('Course:ThreadService', [
            [
                'functionName' => 'countThreads',
                'withParams' => [['courseIds' => [$classroomCourse['courseId']], 'userId' => $member['userId'], 'type' => 'discussion']],
                'returnValue' => 1,
            ],
            [
                'functionName' => 'countThreads',
                'withParams' => [['courseIds' => [$classroomCourse['courseId']], 'userId' => $member['userId'], 'type' => 'question']],
                'returnValue' => 2,
            ],
        ]);

        $this->mockBiz('Thread:ThreadService', [
            [
                'functionName' => 'searchThreadCount',
                'withParams' => [['targetType' => 'classroom', 'targetId' => $classroom['id'], 'userId' => $member['userId'], 'type' => 'discussion']],
                'returnValue' => 1,
            ],
            [
                'functionName' => 'searchThreadCount',
                'withParams' => [['targetType' => 'classroom', 'targetId' => $classroom['id'], 'userId' => $member['userId'], 'type' => 'question']],
                'returnValue' => 2,
            ],
        ]);

        $processor = new ClassroomThreadEventProcessor($this->getBiz());
        $processor->onThreadDelete(new Event($thread));

        $memberResult = $this->getClassroomMemberDao()->get($member['id']);
        $classroomResult = $this->getClassroomDao()->get($classroom['id']);

        $this->assertEquals('5', $classroom['threadNum']);
        $this->assertEquals('10', $classroom['postNum']);

        $this->assertEquals('5', $member['threadNum']);
        $this->assertEquals('10', $member['questionNum']);
        $this->assertEquals('4', $classroomResult['threadNum']);
        $this->assertEquals('5', $classroomResult['postNum']);
        $this->assertEquals('2', $memberResult['threadNum']);
        $this->assertEquals('4', $memberResult['questionNum']);
    }

    public function testOnPostCreate_whenIsTeacher_thenSetAdopt()
    {
        $classroom = $this->createClassroom([]);
        $member = $this->createClassroomMember(['userId' => $this->getCurrentUser()->getId(), 'classroomId' => $classroom['id'], 'role' => ['teacher']]);

        $post = [
            'targetId' => $classroom['id'],
            'userId' => $member['userId'],
        ];

        $threadService = $this->mockBiz('Thread:ThreadService', [
            [
                'functionName' => 'setPostAdopted',
            ],
            [
                'functionName' => 'setThreadSolved',
            ],
        ]);

        $processor = new ClassroomThreadEventProcessor($this->getBiz());
        $processor->onPostCreate(new Event($post));
        $classroomResult = $this->getClassroomDao()->get($classroom['id']);

        $threadService->shouldHaveReceived('setPostAdopted')->times(1);
        $threadService->shouldHaveReceived('setThreadSolved')->times(1);
        $this->assertEquals('0', $classroom['postNum']);
        $this->assertEquals('1', $classroomResult['postNum']);
    }

    public function testOnPostCreate()
    {
        $classroom = $this->createClassroom([]);
        $member = $this->createClassroomMember(['userId' => $this->getCurrentUser()->getId(), 'classroomId' => $classroom['id']]);

        $post = [
            'targetId' => $classroom['id'],
            'userId' => $member['userId'],
        ];

        $threadService = $this->mockBiz('Thread:ThreadService', [
            [
                'functionName' => 'setPostAdopted',
            ],
            [
                'functionName' => 'setThreadSolved',
            ],
        ]);

        $processor = new ClassroomThreadEventProcessor($this->getBiz());
        $processor->onPostCreate(new Event($post));
        $classroomResult = $this->getClassroomDao()->get($classroom['id']);
        $threadService->shouldNotHaveReceived('setPostAdopted');
        $threadService->shouldNotHaveReceived('setThreadSolved');
        $this->assertEquals('0', $classroom['postNum']);
        $this->assertEquals('1', $classroomResult['postNum']);
    }

    public function testOnPostDelete()
    {
        $post = [
            'targetId' => 1,
            'userId' => $this->getCurrentUser()->getId(),
            'threadId' => 1,
        ];

        $threadService = $this->mockBiz('Thread:ThreadService', [
            [
                'functionName' => 'searchPostsCount',
                'returnValue' => 0,
            ],
            [
                'functionName' => 'cancelThreadSolved',
            ],
        ]);

        $processor = new ClassroomThreadEventProcessor($this->getBiz());
        $processor->onPostDelete(new Event($post, ['deleted' => 1]));

        $threadService->shouldHaveReceived('cancelThreadSolved')->times(1);
    }

    private function createClassroom(array $classroom = [])
    {
        return $this->getClassroomDao()->create(array_merge([
            'title' => 'test classroom',
            'threadNum' => 0,
        ], $classroom));
    }

    private function createClassroomMember(array $member = [])
    {
        return $this->getClassroomMemberDao()->create(array_merge([
            'classroomId' => 1,
            'userId' => 1,
            'role' => ['student'],
        ], $member));
    }

    private function createClassroomCourse(array $course = [])
    {
        return $this->getClassroomCourseDao()->create(array_merge([
            'classroomId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'parentCourseId' => 1,
        ], $course));
    }

    /**
     * @return ClassroomDao
     */
    private function getClassroomDao()
    {
        return $this->getBiz()->dao('Classroom:ClassroomDao');
    }

    /**
     * @return ClassroomMemberDao
     */
    private function getClassroomMemberDao()
    {
        return $this->getBiz()->dao('Classroom:ClassroomMemberDao');
    }

    /**
     * @return ClassroomCourseDao
     */
    private function getClassroomCourseDao()
    {
        return $this->getBiz()->dao('Classroom:ClassroomCourseDao');
    }
}
