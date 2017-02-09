<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join'        => 'onCourseJoin',
            'course.quit'        => 'onMemberDelete',

            'course.task.delete' => 'onTaskDelete',
            'course.task.finish' => 'onTaskFinish'
        );
    }

    public function onCourseJoin(Event $event)
    {
        $this->countStudentMember($event);
        $this->countIncome($event);
        $this->sendWelcomeMsg($event);
    }

    private function countStudentMember(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ($member['role'] == 'student') {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    private function countIncome(Event $event)
    {
        $course = $event->getSubject();

        $income = $this->getOrderService()->sumOrderPriceByTarget('course', $course['id']);
        $this->getCourseDao()->update($course['id'], array('income' => $income));
    }

    private function sendWelcomeMsg(Event $event)
    {
        $course = $event->getSubject();

        $setting = $this->getSettingService()->get('course', array());
        $biz     = $this->getBiz();
        $user    = $biz['user'];
        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);
            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
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

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $user = $event->getArgument('user');
        $this->updateMemberLearnedNum($task['courseId'], $user['id']);
    }

    protected function getWelcomeMessageBody($user, $course)
    {
        $setting            = $this->getSettingService()->get('course', array());
        $valuesToBeReplace  = array('{{nickname}}', '{{course}}');
        $valuesToReplace    = array($user['nickname'], $course['title']);
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);
        return $welcomeMessageBody;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    protected function getMessageService()
    {
        return $this->getBiz()->service('User:MessageService');
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

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
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

    protected function getCourseDao()
    {
        return $this->getBiz()->Dao('Course:CourseDao');
    }
}
