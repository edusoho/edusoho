<?php
 
namespace Topxia\WebBundle\Handler;
 
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
		
		list($courses, $courseMembers) = $this->getCourseService()->findWillOverdueCourses();
		$courseMembers = ArrayToolkit::index($courseMembers, "courseId");

		foreach ($courses as $key => $course) {
			$courseUrl = $this->generateUrl('course_show', array('id'=>$course['id']));
			$this->getNotificationService()->notify($user["id"], "default", 
                "您加入的课程<a href='{$courseUrl}'>《{$course['title']}》</a>将在".date("Y年m月d日",$courseMembers[$course["id"]]["deadline"])."到期。");
			$courseMemberId = $courseMembers[$course["id"]]["id"];
			$this->getCourseService()->updateCourseMember($courseMemberId, array("deadlineNotified"=>1));
		}

		$vipApp = $this->getAppService()->findInstallApp('Vip');
		if(!empty($vipApp) && version_compare($vipApp['version'], "1.0.5", ">=")){
			$vipSetting = $this->getSettingService()->get('vip', array());
			if(array_key_exists("deadlineNotify", $vipSetting) && $vipSetting["deadlineNotify"] == 1) {
				$vip = $this->getVipService()->getMemberByUserId($user["id"]);
				$currentTime = time();
				if($vip["deadlineNotified"] != 1 && $currentTime < $vip["deadline"] && ($currentTime + $vipSetting["daysOfNotifyBeforeDeadline"]*24*60*60) > $vip["deadline"]) {
					$this->getNotificationService()->notify($user["id"], "default", 
                			"您购买的会员将在".date("Y年m月d日",$vip["deadline"])."到期。");
					$this->getVipService()->updateDeadlineNotified($vip["id"], 1);
				}
			}
		}

	}

	public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

	private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
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
}