<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onMemberCreate',
            'course.quit' => 'onMemberDelete',

            'course.task.delete' => 'onTaskDelete',
            'course.task.finish' => 'onTaskFinish',
        );
    }

    public function onMemberCreate(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ($member['role'] == 'student') {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    public function onMemberDelete(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ($member['role'] == 'student') {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $user       = $event->getArgument('user');
        $this->updateMemberLearnedNum($taskResult['courseId'], $user['id']);
    }

    protected function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $user = $event->getArgument('user');
        $this->updateMemberLearnedNum($task['courseId'], $user['id']);
    }


    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function updateMemberLearnedNum($courseId, $userId)
    {
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);

        $conditions = array(
            'status'   => 'finish',
            'courseId' => $courseId,
            'userId'   => $userId
        );
        $learnedNum = $this->getTaskResultService()->countTaskResults($conditions);

        $this->getCourseMemberService()->updateMember($member['id'], array('learnedNum' => $learnedNum));
    }
}