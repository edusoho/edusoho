<?php

namespace Biz\Course\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Order\Service\OrderService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\User\Service\MessageService;
use Biz\User\Service\StatusService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

            'course.task.delete' => 'onTaskDelete',
            'course.task.finish' => 'onTaskFinish',
        );
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
        //add classroom students to course
        $existedMembers = $this->getCourseMemberService()->findCourseStudents($course['id'], 0, PHP_INT_MAX);
        $diffMemberIds = $memberIds;
        if (!empty($existedMembers)) {
            $existedMemberIds = ArrayToolkit::column($existedMembers, 'userId');
            $diffMemberIds = array_diff($memberIds, $existedMemberIds);
        }

        if (empty($diffMemberIds)) {
            return;
        }

        foreach ($diffMemberIds as $memberId) {
            $this->getCourseMemberService()->becomeStudent($course['id'], $memberId);
        }
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
        $userId = $event->getArgument('userId');
        $user = $this->getUserService()->getUser($userId);

        $setting = $this->getSettingService()->get('course', array());

        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);

            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
        }
    }

    private function publishStatus($event, $type)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        $status = array(
            'type' => $type,
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
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

        if ($member['role'] == 'student') {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $user = $event->getArgument('user');
        $this->updateMemberLearnData($taskResult['courseId'], $user['id']);
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $user = $event->getArgument('user');
        $this->updateMemberLearnedNum($task['courseId'], $user['id']);
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

    protected function updateMemberLearnedNum($courseId, $userId)
    {
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);

        $conditions = array(
            'status' => 'finish',
            'courseId' => $courseId,
            'userId' => $userId,
        );
        $learnedNum = $this->getTaskResultService()->countTaskResults($conditions);

        $this->getCourseMemberService()->updateMember($member['id'], array('learnedNum' => $learnedNum));
    }

    private function updateMemberLearnData($courseId, $userId)
    {
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);

        $conditions = array(
            'status' => 'finish',
            'courseId' => $courseId,
            'userId' => $userId,
        );
        $learnedNum = $this->getTaskResultService()->countTaskResults($conditions);

        $learnData = array(
            'learnedNum' => $learnedNum,
            'lastLearnTime' => time(),
        );

        $this->getCourseMemberService()->updateMember($member['id'], $learnData);
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }
}
