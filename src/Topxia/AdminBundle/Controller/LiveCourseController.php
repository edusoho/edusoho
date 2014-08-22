<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class LiveCourseController extends BaseController
{

    public function indexAction (Request $request, $status)
    {   

        $courses = $this->getCourseService()->searchCourses(array('type' => 'live','status' => 'published'), $sort = 'latest', 0, 1000);

        $courseIds = ArrayToolkit::column($courses, 'id');

        $courses = ArrayToolkit::index($courses, 'id');

        $conditions['type']="live";
        
        switch ($status) {
            case 'coming':
                $conditions['startTimeGreaterThan'] = time();
                break;
            case 'end':
                $conditions['endTimeLessThan'] = time();
                break;
            case 'underway':
                $conditions['startTimeLessThan'] = time();
                $conditions['endTimeGreaterThan'] = time();
                break;
        }

        $conditions['courseIds'] = $courseIds;
        $conditions['status'] ='published';

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchLessonCount($conditions),
            20
        );

        if ($status == 'end') {
            $lessons = $this->getCourseService()->searchLessons($conditions, 
                array('startTime', 'DESC'), 
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        } else {
            $lessons = $this->getCourseService()->searchLessons($conditions, 
                array('startTime', 'ASC'), 
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }
        
        return $this->render('TopxiaAdminBundle:LiveCourse:index.html.twig', array(
            'status' => $status,
            'lessons' => $lessons,
            'courses' => $courses,
            'paginator' => $paginator
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}