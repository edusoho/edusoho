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

            'course.member.import' =>'onCourseMemberImport',
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
        file_put_contents('2.txt', 'join');
        $this->countStudentMember($event);
        $this->countIncome($event);
        $this->sendWelcomeMsg($event);
        $this->publishStatus($event, 'become_student');
    }

    public function onCourseTeachersCreate(Event $event)
    {
        $course = $event->getArgument('course');

        $teachers = $this->getCourseMemberService()->findCourseTeachers($course['id']);
        $teachers = ArrayToolkit::index($teachers, 'userId');

        $teacherIds = $event->getSubJect();
        $teacherIds = array_values($teacherIds);
        $users = $this->getUserService()->findUsersByIds($teacherIds);
        $users = ArrayToolkit::index($users, 'id');

        list($activities, $liveActivities) = $this->findActivitiesAndLiveActivities($course);

        foreach ($activities as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }

            foreach ($teacherIds as $userId) {
                $result[] = $this->buildLiveMemberData($users[$userId], $teachers[$userId]);
            }
            $this->pushJoinLiveCourseMember($result, $liveActivities[$mediaId]['liveId']);
        }
    }

    public function onCourseTeachersDelete(Event $event)
    {
        $course = $event->getArgument('course');

        $teacherIds = $event->getSubJect();
        list($activities, $liveActivities) = $this->findActivitiesAndLiveActivities($course);
        foreach ($activities as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }

            foreach ($teacherIds as $teacherId) {
                $this->pushDeleteLiveCourseMember($teacherId, $liveActivities[$mediaId]['liveId']);
            }
        }
    }

    public function onLiveCourseJoin(Event $event)
    {
        file_put_contents('1.txt', 'live');
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $user = $this->getUserService()->getUser($userId);
        $member = $event->getArgument('member');

        list($activities, $liveActivities) = $this->findActivitiesAndLiveActivities($course);
        foreach ($activities as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }
            $result[] = $this->buildLiveMemberData($user, $member);
            $this->pushJoinLiveCourseMember($result, $liveActivities[$mediaId]['liveId']);
        }
    }

    public function onClassroomLiveCourseJoin(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');
        $user = $this->getUserService()->getUser($member['userId']);

        if ($course['locked']) {
            $course = $this->getCourseService()->getCourse($course['parentId']);
        }

        list($activities, $liveActivities) = $this->findActivitiesAndLiveActivities($course);
        foreach ($activities as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }
            $result[] = $this->buildLiveMemberData($user, $member);
            $this->pushJoinLiveCourseMember($result, $liveActivities[$mediaId]['liveId']);
        }
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
            $result = $api->post("/lives/{$liveId}/room_members", array('members' => $result));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(ServiceKernel::instance()->trans('发送失败！'));
        }
    }

    public function pushDeleteLiveCourseMember($userId, $liveId)
    {
        try {
            $api = CloudAPIFactory::create('root');
            $result = $api->delete("/lives/{$liveId}/room_members", array('clientId' => $userId));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(ServiceKernel::instance()->trans('发送失败！'));
        }
    }

    public function onClassroomCourseJoin(Event $event)
    {
        $this->publishStatus($event, 'become_student');
    }

    public function onCourseMemberImport(Event $event)
    {
        $members = $event->getArgument('members');
        $members = ArrayToolkit::index($members, 'userId');
        $userIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $course = $event->getSubject();
        list($activities, $liveActivities) = $this->findActivitiesAndLiveActivities($course);

        foreach ($activities as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }

            foreach ($members as $userId => $member) {
                $result[] = $this->buildLiveMemberData($users[$userId], $member);
            }
            $this->pushJoinLiveCourseMember($result, $liveActivities[$mediaId]['liveId']);
        }
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
        $userId = $event->getArgument('userId');

        if ($course['locked']) {
            $course = $this->getCourseService()->getCourse($course['parentId']);
        }
        list($activities, $liveActivities) = $this->findActivitiesAndLiveActivities($course);
        foreach ($activities as $mediaId => $activity) {
            $isPush = $this->canPushLiveMessage($activity);
            if (!$isPush) {
                continue;
            }
            $this->pushDeleteLiveCourseMember($userId, $liveActivities[$mediaId]['liveId']);
        }
    }

    protected function canPushLiveMessage($activity)
    {
        if ($activity['mediaType'] != 'live') {
            return false;
        }
        if (time() > $activity['endTime']) {
            return false;
        }
        return true;
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
        $setting = $this->getSettingService()->get('course', array());
        $valuesToBeReplace = array('{{nickname}}', '{{course}}');
        $valuesToReplace = array($user['nickname'], $course['title']);
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);

        return $welcomeMessageBody;
    }

    protected function findActivitiesAndLiveActivities($course)
    {
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($course['id'], 'live');
        $activities = ArrayToolkit::index($activities, 'mediaId');
        $liveActivities = $this->getLiveActivityService()->findLiveActivitiesByIds(ArrayToolkit::column($activities, 'mediaId'));
        $liveActivities = ArrayToolkit::index($liveActivities, 'id');

        return array($activities, $liveActivities);
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

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

   protected function getLiveActivityService()
   {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }
}
