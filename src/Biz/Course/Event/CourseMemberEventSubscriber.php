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
use Topxia\Service\Common\ServiceKernel;
use Biz\CloudPlatform\CloudAPIFactory;

class CourseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onCourseJoin',
            'course.join' => 'onLiveCourseJoin',
            'course.quit' => 'onMemberDelete',
            'course.quit' => 'onLiveMemberDelete',
            'course.view' => 'onCourseView',

            'classroom.course.join' => 'onClassroomCourseJoin',
            'classroom.course.join' => 'onClassroomLiveCourseJoin',
            'classroom.course.copy' => 'onClassroomCourseCopy',

            'course.task.delete' => 'onTaskDelete',
            'course.task.finish' => 'onTaskFinish',

            'course.teachers.create' =>'onCourseTeachersCreate',
            'course.teachers.delete' =>'onCourseTeachersDelete',
        );
    }

    public function onCourseView(Event $event)
    {
        $course = $event->getSubJect();
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

    public function onCourseTeachersCreate(Event $event)
    {
        $teacherIds = $event->getSubJect();
        $teachers = $this->getCourseMember()->findMembersByUserIds($teacherIds);
        $teachers = ArrayToolkit::index($teachers, 'userId');

        $users = $this->getUserService()->findUsersByIds($teacherIds);
        $users = ArrayToolkit::index($users, 'id');

        $course = $event->getArgument('course');
        list($activitys, $liveActivitys) = $this->findActivitysAndLiveActivitys($course);

        foreach ($activitys as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }

            foreach ($teachers as $userId => $teacher) {
                $result = buildLiveMemberData($users[$userId], $teacher);
                $this->pushJoinLiveCourseMember($result, $liveActivitys[$mediaId]['liveId']);
            }
        }
    }

    public function onCourseTeachersDelete(Event $event)
    {
        $course = $event->getArgument('course');
        $isPush = $this->canPushLiveMessage($course);
        if (!$isPush) {
            return;
        }

        $teacherIds = $event->getSubJect();
        foreach ($teacherIds as $teacherId) {
            $this->pushDeleteLiveCourseMember($teacherId);
        }
    }

    public function onLiveCourseJoin(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $user = $this->getUserService()->getUser($userId);
        $member = $event->getArgument('member');

        list($activitys, $liveActivitys) = $this->findActivitysAndLiveActivitys($course);
        foreach ($activitys as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }
            $result = buildLiveMemberData($user, $member);
            $this->pushJoinLiveCourseMember($result, $liveActivitys[$mediaId]['liveId']);
        }
    }

    public function onClassroomLiveCourseJoin(Event $event)
    {
        // $course = $event->getSubject();
        // $member = $event->getArgument('member');
        // $user = $this->getUserService()->getUser($member['userId']);

        // $activitys = $this->getActivityService()->findActivitiesByCourseIdAndType($course['id'], 'live');
        // foreach ($activitys as $activity) {
        //     $isPush = $this->canPushLiveMessage($activity);
        //     if (!$isPush) {
        //         continue;
        //     }
        //     $result = buildLiveMemberData($user, $teacher);
        //     $this->pushJoinLiveCourseMember($result, $liveId);
        // }
    }

    protected function getFileUrl($path, $default = '')
    {
        if (empty($path)) {
            if (empty($default)) {
                return '';
            }
            $path = $this->getHttpHost().'/assets/img/default/'.$default;
            return $path;
        };
        
        if (strpos($path, $this->getHttpHost().'://') !== false) {
            return $path;
        }
        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = $this->getHttpHost().'/files/'.ltrim($path, '/');

        return $path;
    }

    protected function getHttpHost()
    {
        $schema = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) ? 'https' : 'http';

        return $schema."://{$_SERVER['HTTP_HOST']}";
    }

    protected function buildLiveMemberData($user, $member)
    {
        $result['clientName'] = $user['nickname'];
        $result['clientId'] = $user['id'];
        $result['avatar'] = $this->getFileUrl($user['smallAvatar'], 'avatar.png');
        $result['role'] = $member['role'];
        
        return $result;
    }

    protected function pushJoinLiveCourseMember($result, $liveId)
    {
        try {
            $api = CloudAPIFactory::create('root');
            $result = $api->post("/lives/{$liveId}/room_members", array($result));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(ServiceKernel::instance()->trans('发送失败！'));
        }
    }

    public function pushDeleteLiveCourseMember($userId)
    {
        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->delete('/lives/room_members', array('clientId' => $userId));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(ServiceKernel::instance()->trans('发送失败！'));
        }
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

    public function onLiveMemberDelete(Event $event)
    {
        $course = $event->getSubject();
        $isPush = $this->canPushLiveMessage($course);
        if (!$isPush) {
            return;
        }
        $userId = $event->getArgument('userId');
        $this->pushDeleteLiveCourseMember($userId);
    }

    protected function canPushLiveMessage($activity)
    {
        if ($activity['type'] != 'live') {
            return false;
        }
        if (time() < $activity['startTime'] || time() > $activity['endTime']) {
            return false;
        }
        return true;
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $user = $event->getArgument('user');
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
        $setting = $this->getSettingService()->get('course', array());
        $valuesToBeReplace = array('{{nickname}}', '{{course}}');
        $valuesToReplace = array($user['nickname'], $course['title']);
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);

        return $welcomeMessageBody;
    }

    protected function findActivitysAndLiveActivitys($course)
    {
        $activitys = $this->getActivityService()->findActivitiesByCourseIdAndType($course['id'], 'live');
        $activitys = ArrayToolkit::index($activitys, 'mediaId');
        $liveActivitys = $this->getLiveActivityService()->findLiveActivity(ArrayToolkit::column($activitys, 'mediaId'));
        $liveActivitys = ArrayToolkit::index($liveActivitys, 'id');

        return array($activitys, $liveActivitys);        
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

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

   protected function getLiveActivityService()
   {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }
}
