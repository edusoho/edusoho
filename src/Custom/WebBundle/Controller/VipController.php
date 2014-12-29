<?php
namespace Custom\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class VipController extends BaseController
{
    public function indexAction(Request $request)
    {	  
        
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $deadlineAlertCookie = $request->cookies->get('deadlineAlert');

    	$conditions = array();
        $members = $this->getVipService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 9);
        $memberIds = ArrayToolkit::column($members,'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);

    	$levels = $this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100);

        $currentUser = $this->getCurrentUser();
        $userVip =  $currentUser->isLogin() ? $this->getVipService()->getMemberByUserId($currentUser['id']) : null;
       
        $conditions['vipLevelIdGreaterThan'] = 1;
        $sort = $request->query->get('sort', 'latest');

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

        return $this->render('VipBundle:Vip:index.html.twig',array(
        	'levels' => ArrayToolkit::index($levels, 'id'),
            'latestMembers' => $latestMembers,
            'members'=> $members,
            'userVip'=> $userVip,
            'deadlineAlertCookie' => $deadlineAlertCookie,
            'nowTime' => time(),
            'consultDisplay' => true,
            'courses' => $courses,
            'paginator' => $paginator
        ));
    }

    public function courseAction(Request $request ,$levelId)
    {   
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        if (!empty($levelId)) {
            $level = $this->getLevelService()->getLevel($levelId);
            if (empty($level)) {
                throw $this->createNotFoundException();
            }
        } else {
            $level = array('id' => null);
        }

        $conditions = array('status' => 'published');

        if (!empty($level['id'])) {
            $vipLevelIds = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($level['id']), 'id');
            $conditions['vipLevelIds'] = array_merge(array($level['id']), $vipLevelIds);
        } else {
            $conditions['vipLevelIdGreaterThan'] = 1;
        }

        $sort = $request->query->get('sort', 'latest');

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

        $levels = $this->getLevelService()->findEnabledLevels();

        return $this->render('VipBundle:Vip:course.html.twig',array(
            'levels' => $levels,
            'courses' => $courses,
            'paginator' => $paginator,
            'level' => $level,
            'sort' => $sort,
        ));
    }

    public function historyAction(Request $request)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $deadlineAlertCookie = $request->cookies->get('deadlineAlert');

        $conditions = array();
        
        $currentUser = $this->getCurrentUser();
        $members = $this->getVipService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 10);
        $memberIds = ArrayToolkit::column($members,'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);
        $levels = $this->getLevelService()->searchLevels( array('enabled' => 1), 0, 100);
        $member = $this->getVipService()->getMemberByUserId($currentUser['id']);

        $conditions = array('nickname' => $currentUser['nickname']);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getVipService()->searchMembersHistoriesCount($conditions)
            ,20
        );

        $memberHistories = $this->getVipService()->searchMembersHistories(
            $conditions, array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('VipBundle:Vip:history.html.twig',array(
            'levels' => $levels,
            'latestMembers' => $latestMembers,
            'members' => $members,
            'userVip' => $member,
            'nowTime' => time(),
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
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