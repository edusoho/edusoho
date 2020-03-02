<?php

namespace Biz\Course\Event;

use AppBundle\Common\MathToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\User\Service\MessageService;
use Biz\User\Service\StatusService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Common\ArrayToolkit;

class CourseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onCourseJoin',
            'course.quit' => 'onMemberDelete',
            'course.view' => 'onCourseView',
            'task.view' => 'onTaskView',
            'classroom.course.join' => 'onClassroomCourseJoin',
            'classroom.course.copy' => 'onClassroomCourseCopy',
            'course.delete' => array('onCourseDelete', -100),
            'course.task.finish' => 'onTaskFinish',
        );
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
        $this->getCourseMemberService()->deleteMemberByCourseId($course['id']);
    }

    public function onTaskView(Event $event)
    {
        $courseMember = $event->getSubject();
        if (!empty($courseMember)) {
            $fields['lastLearnTime'] = time();
            $this->getCourseMemberService()->updateMember($courseMember['id'], $fields);
        }
    }

    public function onCourseView(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $userId);
        if (!empty($member)) {
            $fields['lastViewTime'] = time();
            $this->getCourseMemberService()->updateMember($member['id'], $fields);
        }
    }

    public function onCourseJoin(Event $event)
    {
        $this->countStudentMember($event);
        $this->countIncome($event);
        $this->sendWelcomeMsg($event);
        $this->publishStatus($event, 'become_student');
    }

    public function onClassroomCourseJoin(Event $event)
    {
        $this->publishStatus($event, 'become_student');
    }

    public function onClassroomCourseCopy(Event $event)
    {
        $course = $event->getSubject();
        $classroomId = $event->getArgument('classroomId');
        $members = $this->getClassroomService()->findClassroomStudents($classroomId, 0, PHP_INT_MAX);
        if (empty($members)) {
            return;
        }

        $memberIds = ArrayToolkit::column($members, 'userId');
        $this->getCourseMemberService()->batchBecomeStudents($course['id'], $memberIds, $classroomId);
    }

    private function countStudentMember(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ('student' == $member['role']) {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    private function countIncome(Event $event)
    {
        $course = $event->getSubject();

        $conditions = array(
            'target_id' => $course['id'],
            'target_type' => 'course',
            'statuses' => array('paid', 'success', 'finished'),
        );

        $income = $this->getOrderFacadeService()->sumOrderItemPayAmount($conditions);
        $income = MathToolkit::simple($income, 0.01);

        $this->getCourseDao()->update($course['id'], array('income' => $income));
    }

    private function sendWelcomeMsg(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $user = $this->getUserService()->getUser($userId);

        $setting = $this->getSettingService()->get('course', array());

        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);

            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
        }
    }

    private function publishStatus(Event $event, $type)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        $status = array(
            'type' => $type,
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => 'published' == $course['status'] ? 0 : 1,
            'userId' => $member['userId'],
            'properties' => array(
                'course' => $this->simplifyCourse($course),
            ),
        );

        $this->getStatusService()->publishStatus($status);
    }

    public function onMemberDelete(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');
        $this->countIncome($event);

        if ('student' == $member['role']) {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $this->getCourseService()->recountLearningData($taskResult['courseId'], $taskResult['userId']);
        $finishTime = $this->getCourseFinishTime($taskResult);

        $this->getCourseMemberService()->updateMembers(
            array('courseId' => $taskResult['courseId'], 'userId' => $taskResult['userId']),
            array('lastLearnTime' => time(), 'finishedTime' => $finishTime)
        );
    }

    private function getCourseFinishTime($taskResult)
    {
        $student = $this->getCourseMemberService()->getCourseMember($taskResult['courseId'], $taskResult['userId']);
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        if (0 == $course['compulsoryTaskNum']) {
            $isFinished = false;
        } else {
            $isFinished = intval($student['learnedCompulsoryTaskNum'] / $course['compulsoryTaskNum']) >= 1 ? true : false;
        }
        $finishTime = $isFinished ? time() : 0;

        return $finishTime;
    }

    protected function getWelcomeMessageBody($user, $course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $setting = $this->getSettingService()->get('course', array());
        $valuesToBeReplace = array('{{nickname}}', '{{course}}');
        $valuesToReplace = array($user['nickname'], ' '.$courseSet['title'].'-'.$course['title'].' ');
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);

        return $welcomeMessageBody;
    }

    protected function simplifyCourse($course)
    {
        return array(
            'id' => $course['id'],
            'title' => $course['title'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'price' => $course['price'],
        );
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->getBiz()->service('OrderFacade:OrderFacadeService');
    }

    /**
     * @return MessageService
     */
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }
}
