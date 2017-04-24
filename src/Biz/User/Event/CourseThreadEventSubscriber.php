<?php

namespace Biz\User\Event;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\User\Service\StatusService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseThreadEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.post.create' => 'onThreadPostCreate',
        );
    }

    public function onThreadPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $thread = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);

        $course = $this->getCourseService()->getCourse($post['courseId']);

        if ($course['parentId']) {
            $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);
            $classroom = array_shift($classrooms);
            $isTeacher = $this->getClassroomService()->isClassroomTeacher($classroom['classroomId'], $post['userId']);
        } else {
            $isTeacher = $this->getCourseMemberService()->isCourseTeacher($post['courseId'], $post['userId']);
        }

        if ($isTeacher && $thread['type'] == 'question') {
            $this->getStatusService()->publishStatus(array(
                'userId' => $thread['userId'],
                'courseId' => $post['courseId'],
                'type' => 'teacher_thread_post',
                'objectType' => 'thread_post',
                'objectId' => $post['id'],
                'private' => $course['status'] == 'published' ? 0 : 1,
                'properties' => array(
                    'thread' => $thread,
                    'post' => $post,
                ),
            ));
        }
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
