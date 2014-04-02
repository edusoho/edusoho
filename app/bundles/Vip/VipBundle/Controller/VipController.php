<?php
namespace Vip\VipBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class VipController extends BaseController
{
    public function indexAction(Request $request)
    {	  

        $vipSetting = $this->getSettingService()->get('memberZone', array());

        $deadlineAlertCookie = $request->cookies->get('deadlineAlert');

    	$conditions = array();
        $members = $this->getVipService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 10);
        $memberIds = ArrayToolkit::column($members,'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);

    	$levels = $this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100);
        $levelIds = ArrayToolkit::column($levels,'id');
        $latestCourses = $this->getCourseService()->searchCourses(array('status' => 'published','memberLevelIds' => $levelIds), $sort = 'latest', 0, 3);
        $hotestCourses = $this->getCourseService()->searchCourses(array('status' => 'published','memberLevelIds' => $levelIds), $sort = 'popular', 0, 3);

        $currentUser = $this->getCurrentUser();
        $userVip =  $currentUser->isLogin() ? $this->getVipService()->getMemberByUserId($currentUser['id']) : null;

        return $this->render('VipBundle:Vip:index.html.twig',array(
        	'levels' => $levels,
            'latestCourses' => $latestCourses,
            'hotestCourses' => $hotestCourses,
            'latestMembers' => $latestMembers,
            'members'=> $members,
            'userVip'=> $userVip,
            'vipSetting'=> $vipSetting,
            'deadlineAlertCookie' => $deadlineAlertCookie
        ));
    }

    public function courseAction(Request $request ,$levelId)
    {   
        $vipSetting = $this->getSettingService()->get('memberZone', array());

        if (!empty($levelId)) {
            if (ctype_digit((string) $levelId)) {
                $level = $this->getLevelService()->getLevel($levelId);
            }
            if (empty($level)) {
                throw $this->createNotFoundException();
            }
        } else {
            $level = array('id' => null);
        }
        if (empty($level['id'])) {
            $memberlevelIds = null;
        } else {
            $memberlevels = $this->getLevelService()->findLevelsBySeq($level['seq'], 0, 100);
            $memberlevelIds = ArrayToolkit::column($memberlevels,'id');
        }

        $sort = $request->query->get('sort', 'latest');

        $vipSetting = $this->getSettingService()->get('memberZone', array());

        if ($vipSetting['courseLimit'] == 1) {
             $conditions = array(
                'status' => 'published',
                'memberLevelIds' => array($level['id'])
            );
        } else {
            $conditions = array(
                'status' => 'published',
                'memberLevelIds' => $memberlevelIds
            );
        }
        
        if ($conditions['memberLevelIds'][0] == null OR $conditions['memberLevelIds'] == null) {
            unset($conditions['memberLevelIds']);
            $conditions['memberLevelIdGreaterThan'] = 1;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            , 9
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $levels = $this->getLevelService()->searchLevels(array(), 0, 100);

        return $this->render('VipBundle:Vip:course.html.twig',array(
            'levels' => $levels,
            'courses' => $courses,
            'paginator' => $paginator,
            'level' => $level,
            'sort' => $sort,
            'memberZone' => $vipSetting
        ));
    }

    public function historyAction(Request $request)
    {   
        $vipSetting = $this->getSettingService()->get('memberZone', array());

        $deadlineAlertCookie = $request->cookies->get('deadlineAlert');

        $conditions = array();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            ,20
        );
        $currentUser = $this->getCurrentUser();
        $members = $this->getVipService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 10);
        $memberIds = ArrayToolkit::column($members,'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);
        $levels = $this->getLevelService()->searchLevels( array('enabled' => 1), 0, 100);
        $member = $this->getVipService()->getMemberByUserId($currentUser['id']);
        $memberHistories = $this->getVipService()->searchMembersHistories(
            array('userId' => $currentUser['id']), array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('VipBundle:Vip:history.html.twig',array(
            'levels' => $levels,
            'latestMembers' => $latestMembers,
            'members' => $members,
            'member' => $member,
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'memberZone' => $vipSetting,
            'deadlineAlertCookie' => $deadlineAlertCookie
         ));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }   

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}