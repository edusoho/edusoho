<?php
namespace Topxia\WebBundle\Handler;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 *
 */
class GenerateNotificationHandler
{
    private $container;


    /**
     * Constructor
     *
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

        $user = ServiceKernel::instance()->getCurrentUser();

        $this->sendCoursesOverdueNotification($user);

        $this->sendClassroomsOverdueNotification($user);

        $this->sendVipsOverdueNotification($user);
    }

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    protected function sendCoursesOverdueNotification($user)
    {
        list($courses, $courseMembers) = $this->getCourseService()->findWillOverdueCourses();
        $courseMembers = ArrayToolkit::index($courseMembers, "courseId");

        foreach ($courses as $key => $course) {
            $message = array(
                'courseId' => $course['id'],
                'courseTitle' => $course['title'],
                'endtime' => date("Y-m-d", $courseMembers[$course["id"]]["deadline"]));
            $this->getNotificationService()->notify($user["id"], "course-deadline", $message);
            $courseMemberId = $courseMembers[$course["id"]]["id"];
            $this->getCourseService()->updateCourseMember($courseMemberId, array("deadlineNotified"=>1));
        }
    }

    protected function sendClassroomsOverdueNotification($user)
    {
        list($classrooms, $classroomMembers) = $this->getClassroomService()->findWillOverdueClassrooms();
        $classroomMembers = ArrayToolkit::index($classroomMembers, 'classroomId');

        foreach ($classrooms as $key => $classroom) {
            $message = array(
                'classroomId'    => $classroom['id'],
                'classroomTitle' => $classroom['title'],
                'endtime'        => date('Y-m-d', $classroomMembers[$classroom['id']]['deadline'])
            );
            $this->getNotificationService()->notify($user['id'], 'classroom-deadline', $message);
            $classroomMemberId = $classroomMembers[$classroom['id']]['id'];
            $this->getClassroomService()->updateMember($classroomMemberId, array('deadlineNotified'=>1));
        }
    }

    protected function sendVipsOverdueNotification($user)
    {
        $vipApp = $this->getAppService()->findInstallApp('Vip');
        if (!empty($vipApp) && version_compare($vipApp['version'], "1.0.5", ">=")) {
            $vipSetting = $this->getSettingService()->get('vip', array());
            if (array_key_exists("deadlineNotify", $vipSetting) && $vipSetting["deadlineNotify"] == 1) {
                $vip = $this->getVipService()->getMemberByUserId($user["id"]);
                $currentTime = time();
                if ($vip["deadlineNotified"] != 1 && $currentTime < $vip["deadline"]
                    && ($currentTime + $vipSetting["daysOfNotifyBeforeDeadline"]*24*60*60) > $vip["deadline"]) {
                    $message = array('endtime' =>date("Y-m-d", $vip["deadline"]));
                    $this->getNotificationService()->notify($user["id"], "vip-deadline", $message);
                    $this->getVipService()->updateDeadlineNotified($vip["id"], 1);
                }
            }
        }
    }

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    private function getAppService()
    {
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getVipService()
    {
        return ServiceKernel::instance()->createService('Vip:Vip.VipService');
    }
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}