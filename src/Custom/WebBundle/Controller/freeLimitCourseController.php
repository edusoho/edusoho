<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;


class freeLimitCourseController extends BaseController
{
    public function freeNowAction(Request $request){
        
        $now = time();
        $conditions = array(
            'status' => 'published',
            'freeEndTime' => $now,
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            , 10
        );

        $sort = 'freeEndTime';
        $courses = $this->getCourseService()->searchCourses(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        return $this->render('CustomWebBundle:freeLimitCourse:free-course-explore.html.twig', array(
            'courses' => $courses,
            'type' => 'now',
            'paginator' => $paginator,
        ));
    }

    public function freeComingAction(Request $request){
        $now = time();
        $conditions = array(
            'status' => 'published',
            'freeStartTime' => $now,
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            , 10
        );
        $sort = 'freeStartTime';
        $courses = $this->getCourseService()->searchCourses(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('CustomWebBundle:freeLimitCourse:free-course-explore.html.twig', array(
            'courses' => $courses,
            'type' => 'coming',
            'paginator' => $paginator,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}