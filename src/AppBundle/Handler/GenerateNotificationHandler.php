<?php

namespace AppBundle\Handler;

use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\User\CurrentUser;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Biz\CloudPlatform\Service\AppService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Biz\Course\Util\CourseTitleUtils;

class GenerateNotificationHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->biz = $this->container->get('biz');
    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $this->biz['user'];

        $this->sendCoursesOverdueNotification($user);

        $this->sendClassroomsOverdueNotification($user);

        $this->sendVipsOverdueNotification($user);
    }

    protected function sendCoursesOverdueNotification(CurrentUser $user)
    {
        list($courses, $courseMembers) = $this->getCourseMemberService()->findWillOverdueCourses();
        $courseMembers = ArrayToolkit::index($courseMembers, 'courseId');

        foreach ((array) $courses as $key => $course) {
            if (0 != $course['parentId']) {
                continue;
            }
            $message = array(
                'courseId' => $course['id'],
                'courseTitle' => CourseTitleUtils::getDisplayedTitle($course),
                'endtime' => date('Y-m-d', $courseMembers[$course['id']]['deadline']),
            );
            $this->courseOverduePush($user, $message);
            $this->getNotificationService()->notify($user['id'], 'course-deadline', $message);
            $courseMemberId = $courseMembers[$course['id']]['id'];
            $this->getCourseMemberService()->updateMember($courseMemberId, array('deadlineNotified' => 1));
        }
    }

    private function courseOverduePush($user, $message)
    {
        $from = array(
            'id' => $message['courseId'],
            'type' => 'course',
        );

        $to = array(
            'id' => $user['id'],
            'type' => 'user',
            'convNo' => $this->getConvNo(),
        );

        $body = array(
            'type' => 'course.deadline',
            'courseId' => $message['courseId'],
            'title' => "《{$message['courseTitle']}》",
            'message' => "您加入的课程《{$message['courseTitle']}》将在{$message['endtime']}到期",
        );

        $this->createPushJob($from, $to, $body);
    }

    protected function sendClassroomsOverdueNotification($user)
    {
        list($classrooms, $classroomMembers) = $this->getClassroomService()->findWillOverdueClassrooms();
        $classroomMembers = ArrayToolkit::index($classroomMembers, 'classroomId');

        foreach ($classrooms as $key => $classroom) {
            $message = array(
                'classroomId' => $classroom['id'],
                'classroomTitle' => $classroom['title'],
                'endtime' => date('Y-m-d', $classroomMembers[$classroom['id']]['deadline']),
            );
            $this->classroomOverduePush($user, $message);
            $this->getNotificationService()->notify($user['id'], 'classroom-deadline', $message);
            $classroomMemberId = $classroomMembers[$classroom['id']]['id'];
            $this->getClassroomService()->updateMember($classroomMemberId, array('deadlineNotified' => 1));
        }
    }

    private function classroomOverduePush($user, $message)
    {
        $from = array(
            'id' => $message['classroomId'],
            'type' => 'classroom',
        );

        $to = array(
            'id' => $user['id'],
            'type' => 'user',
            'convNo' => $this->getConvNo(),
        );

        $body = array(
            'type' => 'classroom.deadline',
            'classroomId' => $message['classroomId'],
            'title' => "《{$message['classroomTitle']}》",
            'message' => "您加入的班级《{$message['classroomTitle']}》将在{$message['endtime']}到期",
        );

        $this->createPushJob($from, $to, $body);
    }

    protected function sendVipsOverdueNotification($user)
    {
        if (!$this->isPluginInstalled('vip')) {
            return false;
        }
        $vipSetting = $this->getSettingService()->get('vip', array());
        if (array_key_exists('deadlineNotify', $vipSetting) && 1 == $vipSetting['deadlineNotify']) {
            $vip = $this->getVipService()->getMemberByUserId($user['id']);
            $currentTime = time();
            if (1 != $vip['deadlineNotified'] && $currentTime < $vip['deadline']
                && ($currentTime + $vipSetting['daysOfNotifyBeforeDeadline'] * 24 * 60 * 60) > $vip['deadline']
            ) {
                $message = array('endtime' => date('Y-m-d', $vip['deadline']), 'levelId' => $vip['levelId']);
                $this->vipOverduePush($user, $message);
                $this->getNotificationService()->notify($user['id'], 'vip-deadline', $message);
                $this->getVipService()->updateDeadlineNotified($vip['id'], 1);
            }
        }
    }

    private function vipOverduePush($user, $message)
    {
        $levelId = $message['levelId'];

        $level = $this->getLevelService()->getLevel($levelId);

        $from = array(
            'id' => 0,
            'type' => 'vip',
        );

        $to = array(
            'id' => $user['id'],
            'type' => 'user',
            'convNo' => $this->getConvNo(),
        );

        $body = array(
            'type' => 'vip.deadline',
            'title' => $level['name'],
            'message' => "您购买的会员将在{$message['endtime']}到期",
        );

        $this->createPushJob($from, $to, $body);
    }

    public function generateUrl(
        $route,
        array $parameters = array(),
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob(array(
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ));

        $this->getQueueService()->pushJob($pushJob);
    }

    private function getConvNo()
    {
        $imSetting = $this->getSettingService()->get('app_im', array());
        $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

        return $convNo;
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }

    /**
     * @return AppService
     */
    private function getAppService()
    {
        return $this->biz->service('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->biz->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    protected function isPluginInstalled($pluginName)
    {
        return $this->container->get('kernel')->getPluginConfigurationManager()->isPluginInstalled($pluginName);
    }
}
