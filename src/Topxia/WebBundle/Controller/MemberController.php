<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MemberController extends BaseController
{
    public function indexAction(Request $request)
    {	

        $currentUser = $this->getCurrentUser();
    	$conditions = array();
        $members = $this->getMemberService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 10);
        $memberIds = ArrayToolkit::column($members,'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);
    	$levels = $this->getLevelService()->searchLevels($conditions,0,100);
        $levelIds = ArrayToolkit::column($levels,'id');
        $latestCourses = $this->getCourseService()->searchCourses(array('memberLevelIds' => $levelIds), $sort = 'latest', 0, 3);
        $hotestCourses = $this->getCourseService()->searchCourses(array('memberLevelIds' => $levelIds), $sort = 'popular', 0, 3);
        $member = $this->getMemberService()->getMemberByUserId($currentUser['id']);
        return $this->render('TopxiaWebBundle:Member:index.html.twig',array(
        	'levels' => $levels,
            'latestCourses' => $latestCourses,
            'hotestCourses' => $hotestCourses,
            'latestMembers' => $latestMembers,
            'members'=>$members,
            'member'=>$member
        ));
    }

    public function courseAction(Request $request)
    {   
        $conditions = array();
        
        $levels = $this->getLevelService()->searchLevels($conditions,0,100);
        return $this->render('TopxiaWebBundle:Member:course.html.twig',array(
            'levels' => $levels
        ));
    }

    public function historyAction(Request $request)
    {   
        $conditions = array();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            ,15
        );
        $currentUser = $this->getCurrentUser();
        $members = $this->getMemberService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 10);
        $memberIds = ArrayToolkit::column($members,'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);
        $levels = $this->getLevelService()->searchLevels($conditions,0,100);
        $member = $this->getMemberService()->getMemberByUserId($currentUser['id']);
        $memberHistories = $this->getMemberService()->searchMembersHistories(
            array('userId' => $currentUser['id']), array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaWebBundle:Member:history.html.twig',array(
            'levels' => $levels,
            'latestMembers' => $latestMembers,
            'members' => $members,
            'member' => $member,
            'memberHistories' => $memberHistories,
            'paginator' => $paginator
         ));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getLevelService()
    {
    	return $this->getServiceKernel()->createService('User.LevelService');
    }

    protected function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMemberService()
    {
        return $this->getServiceKernel()->createService('User.MemberService');
    }
}