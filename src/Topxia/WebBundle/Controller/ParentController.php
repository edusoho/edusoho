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
		

        $leaningCourses = $this->getCourseService()->findUserLeaningCourses(
            $selectedChild['id'],
            0,
            PHP_INT_MAX
        );

        $leanedCourses = $this->getCourseService()->findUserLeanedCourses(
            $selectedChild['id'],
            0,
            PHP_INT_MAX
        );

		return $this->render('TopxiaWebBundle:Parent:child-courses.html.twig',array(
			'selectedChild'=>$selectedChild,
			'courses'=>array_merge($leaningCourses,$leanedCourses)
		));
	}


    public function childTestpapersAction(Request $request)
    {
        $user=$this->getCurrentUser();
        if(!$user->isParent()) {
            return $this->createMessageResponse('error', '您不是家长，不能查看此页面！');
        }
        $selectedChild=$this->getSelectedChild($request->query->get('childId'));
        
        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultsCountByUserId($selectedChild['id']),
            10
        );

        $testpaperResults = $this->getTestpaperService()->findTestpaperResultsByUserId(
            $selectedChild['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $testpapersIds = ArrayToolkit::column($testpaperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpapersIds);
        $testpapers = ArrayToolkit::index($testpapers, 'id');

        $targets = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function($target){
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        return $this->render('TopxiaWebBundle:Parent:child-testpapers.html.twig',array(
            'selectedChild'=>$selectedChild,
            'myTestpaperResults' => $testpaperResults,
            'myTestpapers' => $testpapers,
            'courses' => $courses,
            'paginator' => $paginator
        ));
    }
    public function childThreadsAction(Request $request,$type)
    {
        $user=$this->getCurrentUser();
        if(!$user->isParent()) {
            return $this->createMessageResponse('error', '您不是家长，不能查看此页面！');
        }
        $selectedChild=$this->getSelectedChild($request->query->get('childId'));

        $conditions = array(
            'userId' => $selectedChild['id'],
            'type' => $type,
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        return $this->render('TopxiaWebBundle:Parent:child-'.$type.'s.html.twig',array(
            $type.'Active'=>'active',
            'selectedChild'=>$selectedChild,
            'courses'=>$courses,
            'users'=>$users,
            'threads'=>$threads,
            'paginator' => $paginator
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

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }


}