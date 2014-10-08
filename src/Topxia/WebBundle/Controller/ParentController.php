<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ParentController extends BaseController
{
	const CACHE_NAME = 'parent';

    private $cached;

	function childStatusAction(Request $request)
	{
		$user=$this->getCurrentUser();
		if(!$user->isParent()) {
            return $this->createMessageResponse('error', '您不是家长，不能查看此页面！');
        }
		$selectedChild=$this->getSelectedChild($request->query->get('childId'));
		$statuses=$this->getStatusService()->findStatusesByUserId($selectedChild['id']);
		foreach ($statuses as &$status) {
			$status['time']=date('Y年m月d日',$status['createdTime'])==date('Y年m月d日',time())?'今天':date('Y年m月d日',$status['createdTime']);
		}
		$statuses=ArrayToolkit::group($statuses,'time');
		return $this->render('TopxiaWebBundle:Parent:child-status.html.twig',array(
			'selectedChild'=>$selectedChild,
			'statuses'=>$statuses
		));
	}

	function childCoursesAction(Request $request)
	{
		$user=$this->getCurrentUser();
		if(!$user->isParent()) {
            return $this->createMessageResponse('error', '您不是家长，不能查看此页面！');
        }
		$selectedChild=$this->getSelectedChild($request->query->get('childId'));
		
		return $this->render('TopxiaWebBundle:Parent:child-courses.html.twig',array(
			'selectedChild'=>$selectedChild
		));
	}

	public function childInfoAction(Request $request,$childId)
	{
		$user=$this->getCurrentUser();

		$cachedData=$this->getParentCached();
        $children=$cachedData['children'];
        $classMembers=$cachedData['classMembers'];
        $classes=$cachedData['classes'];
        $selectedChild=empty($childId)?current($children):$children[$childId];
        
        return $this->render('TopxiaWebBundle:Parent:child-info.html.twig',array(
        	'children'=>$children,
			'classMembers'=>$classMembers,
			'classes'=>$classes,
			'selectedChild'=>$selectedChild
        ));
	}

	private function getParentCached()
	{
		if (is_null($this->cached)) {
            $this->cached = $this->getCacheService()->get(self::CACHE_NAME);
            if (is_null($this->cached)) {
                $user=$this->getCurrentUser();
				$relations=$this->getUserService()->findUserRelationsByFromIdAndType($user['id'],'family');
		        $children=$this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'toId'));
		        
		        $classMembers=$this->getClassesService()->findClassMembersByUserIds(ArrayToolkit::column($relations, 'toId'));
		        $classes=$this->getClassesService()->findClassesByIds(ArrayToolkit::column($classMembers, 'classId'));
		        $classMembers=ArrayToolkit::index($classMembers, 'userId');

		        $this->cached['children']=$children;
		        $this->cached['classMembers']=$classMembers;
		        $this->cached['classes']=$classes;
		        $this->getCacheService()->set(self::CACHE_NAME, $this->cached);
            }
        }
        return $this->cached;
	}

	private function getSelectedChild($childId){
		$cachedData=$this->getParentCached();
		$children=$cachedData['children'];
		$selectedChild=empty($childId)?current($children):$children[$childId];
		return $selectedChild;
	}

	protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    protected function getCacheService()
    {
        return $this->getServiceKernel()->createService('System.CacheService');
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

}