<?php

namespace Biz\Course\Event;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\MathToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\Service\GoodsService;
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

class CourseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.join' => 'onCourseJoin',
            'course.quit' => 'onMemberDelete',
            'course.view' => 'onCourseView',
            'task.view' => 'onTaskView',
            'classroom.course.join' => 'onClassroomCourseJoin',
            'classroom.course.copy' => 'onClassroomCourseCopy',
            'course.delete' => ['onCourseDelete', -100],
            'course.task.finish' => 'onTaskFinish',
            'course.task.start' => 'onTaskStart',
            'course.lesson.setOptional' => 'onLessonOptionalChange',
            'course.task.create.sync' => 'onTaskCreateSync',
            'course.task.update.sync' => 'onTaskUpdateSync',
            'course.lesson.delete' => ['onCourseLessonDelete', -100],
            'course.task.delete' => ['onCourseTaskDelete', -100],
            'course.task.create' => ['onCourseTaskCreate', -100],
        ];
    }

    public function onCourseTaskCreate(Event $event)
    {
        $task = $event->getSubject();
        $this->getCourseMemberService()->recountLearningDataByCourseId($task['courseId']);
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

    public function onTaskUpdateSync(Event $event)
    {
        $task = $event->getSubject();

        $copiedTasks = $this->getCopiedTasks($task);
        foreach ($copiedTasks as $copiedTask) {
            $this->getCourseMemberService()->recountLearningDataByCourseId($copiedTask['courseId']);
        }
    }

    public function onTaskCreateSync(Event $event)
    {
        $task = $event->getSubject();

        $copiedTasks = $this->getCopiedTasks($task);
        foreach ($copiedTasks as $copiedTask) {
            $this->getCourseMemberService()->recountLearningDataByCourseId($copiedTask['courseId']);
        }
    }

    private function getCopiedTasks($task)
    {
        $courses = $this->getCourseService()->findCoursesByParentIdAndLocked($task['courseId'], 1);

        return $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], ArrayToolkit::column($courses, 'id'));
    }

    private function countStudentMember(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ('student' == $member['role']) {
            $this->getCourseService()->updateCourseStatistics($course['id'], ['studentNum']);
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], ['studentNum']);
        }
    }

    private function countIncome(Event $event)
    {
        $course = $event->getSubject();

        $specs = $this->getGoodsEntityFactory()->create('course')->getSpecsByTargetId($course['id']);
        if (empty($specs)) {
            return;
        }

        $conditions = [
            'target_id' => $specs['id'],
            'target_type' => 'course',
            'statuses' => ['paid', 'success', 'finished'],
        ];

        $income = $this->getOrderFacadeService()->sumOrderPayAmount($conditions);
        $income = MathToolkit::simple($income, 0.01);

        $this->getCourseDao()->update($course['id'], ['income' => $income]);
    }

    private function sendWelcomeMsg(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $user = $this->getUserService()->getUser($userId);

        $setting = $this->getSettingService()->get('course', []);

        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);

            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
        }
    }

    private function publishStatus(Event $event, $type)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        $status = [
            'type' => $type,
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => 'published' == $course['status'] ? 0 : 1,
            'userId' => $member['userId'],
            'properties' => [
                'course' => $this->simplifyCourse($course),
            ],
        ];

        $this->getStatusService()->publishStatus($status);
    }

    public function onMemberDelete(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');
        $this->countIncome($event);

        if ('student' == $member['role']) {
            $this->getCourseService()->updateCourseStatistics($course['id'], ['studentNum']);
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], ['studentNum']);
        }
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $this->getCourseService()->recountLearningData($taskResult['courseId'], $taskResult['userId']);
        $this->getCourseMemberService()->refreshMemberFinishData($taskResult['courseId'], $taskResult['userId']);
    }

    public function onTaskStart(Event $event)
    {
        $taskResult = $event->getSubject();
        $member = $this->getCourseMemberService()->getCourseMember($taskResult['courseId'], $taskResult['userId']);
        if (empty($member['startLearnTime'])) {
            $this->getCourseMemberService()->updateMember($member['id'], ['startLearnTime' => $taskResult['createdTime']]);
        }
    }

    public function onLessonOptionalChange(Event $event)
    {
        $lesson = $event->getSubject();
        $this->getCourseMemberService()->recountLearningDataByCourseId($lesson['courseId']);
    }

    public function onCourseLessonDelete(Event $event)
    {
        $lesson = $event->getSubject();
        $this->getCourseMemberService()->recountLearningDataByCourseId($lesson['courseId']);
    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $this->getCourseMemberService()->recountLearningDataByCourseId($task['courseId']);
    }

    protected function getWelcomeMessageBody($user, $course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $setting = $this->getSettingService()->get('course', []);
        $valuesToBeReplace = ['{{nickname}}', '{{course}}'];
        $valuesToReplace = empty($course['title']) ? [$user['nickname'], ' '.$courseSet['title'].' '] : [$user['nickname'], ' '.$courseSet['title'].'-'.$course['title'].' '];
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);

        return $welcomeMessageBody;
    }

    protected function simplifyCourse($course)
    {
        return [
            'id' => $course['id'],
            'title' => $course['title'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'price' => $course['price'],
        ];
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

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->getBiz()->service('Goods:GoodsService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->getBiz();

        return $biz['goods.entity.factory'];
    }

    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }
}
