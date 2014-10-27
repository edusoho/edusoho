<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ParentController extends BaseController
{
    function childStatusAction(Request $request,$childId)
    {
        $selectedChild=$this->tryViewChild($childId);
        $statuses=$this->getStatusService()->findStatusesByUserId($selectedChild['id'],0,30);
        $statusCount=$this->getStatusService()->findStatusesByUserIdCount($selectedChild['id']);
        $moreBtnShow=$statusCount>count($statuses)?true:false;

        foreach ($statuses as &$status) {
            $status['time']=date('Y年m月d日',$status['createdTime'])==date('Y年m月d日',time())?'今天':date('Y年m月d日',$status['createdTime']);
        }
        $statuses=ArrayToolkit::group($statuses,'time');
        return $this->render('TopxiaWebBundle:Parent:child-status.html.twig',array(
            'selectedChild'=>$selectedChild,
            'statuses'=>$statuses,
            'moreBtnShow'=>$moreBtnShow,
            'statusCount'=>$statusCount,
            'count'=>0
        ));
    }

    public function childSchedulesAction(Request $request,$childId)
    {
        $selectedChild=$this->tryViewChild($childId);
        $class=$this->getClassesService()->getStudentClass($selectedChild['id']);
        return $this->render('TopxiaWebBundle:Parent:child-schedules.html.twig', array(
            'selectedChild' => $selectedChild,
            'class' => $class,
        )); 
    }

    function moreStatusesAction(Request $request,$childId)
    {
        $selectedChild=$this->tryViewChild($childId);
        $statuses=$this->getStatusService()->findStatusesByUserId($selectedChild['id'],$fields['count']*30,30);
        foreach ($statuses as &$status) {
            $status['time']=date('Y年m月d日',$status['createdTime'])==date('Y年m月d日',time())?'今天':date('Y年m月d日',$status['createdTime']);
            
        }
        $statuses=ArrayToolkit::group($statuses,'time');
        return $this->render('TopxiaWebBundle:Parent:child-status-item.html.twig',array(
            'selectedChild'=>$selectedChild,
            'statuses'=>$statuses
        ));
    }

    function childCoursesAction(Request $request,$childId)
    {
        $selectedChild=$this->tryViewChild($childId);

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


    public function childTestpapersAction(Request $request,$childId)
    {
        $selectedChild=$this->tryViewChild($childId);
        
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
    public function childThreadsAction(Request $request,$childId,$type)
    {
        $selectedChild=$this->tryViewChild($childId);

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
        $selectedChild=$this->tryViewChild($childId);
        $user=$this->getCurrentUser();
        $relations=$this->getUserService()->findUserRelationsByFromIdAndType($user['id'],'family');
        $children=$this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'toId'));
        $class=$this->getClassesService()->getStudentClass($selectedChild['id']);
        return $this->render('TopxiaWebBundle:Parent:child-info.html.twig',array(
            'children'=>$children,
            'class'=>$class,
            'selectedChild'=>$selectedChild
        ));
    }

    private function tryViewChild($selectedChildId){
        $child=$this->getUserService()->getUser($selectedChildId);
        if(!empty($selectedChildId) && empty($child)){
            return $this->createMessageResponse('error', '用户不存在！');
        }

        $user=$this->getCurrentUser();
        if(!$user->isParent()) {
            return $this->createMessageResponse('error', '您不是家长，不能查看此页面！');
        }

        $relations=$this->getUserService()->findUserRelationsByFromIdAndType($user['id'],'family');
        $children=$this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'toId'));
        $selectedChild=empty($selectedChildId)?current($children):$children[$selectedChildId];

        $rela=$this->getUserService()->getUserRelationByFromIdAndToIdAndType($user['id'],$selectedChild['id'],'family');
        if(empty($rela)){
            return $this->createMessageResponse('error', '无法查看其他家长子女信息！');
        }

        $relation=current($relations);
        $selectedChild['relation']=$relation['relation'];
        
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