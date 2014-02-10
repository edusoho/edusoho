<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseTestpaperManageController extends BaseController
{
    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpapersCount($conditions),
            10
        );

        $conditions['target'] = "course-{$course['id']}";

        $testPapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' ,'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($testPapers, 'updatedUserId')); 
        
        return $this->render('TopxiaWebBundle:CourseTestpaperManage:index.html.twig', array(
            'course' => $course,
            'testPapers' => $testPapers,
            'users' => $users,
            'lessons' => $lessons,
            'paginator' => $paginator,

        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}