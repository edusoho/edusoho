<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Util\AvatarAlert;

class MyLiveCourseController extends BaseController
{

    public function indexAction (Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $courses = $this->getCourseService()->findUserLeaningCourses(
            $currentUser['id'], 0, 1000
        );
        $courseIds = ArrayToolkit::column($courses, 'id');

        $conditions = array(
            'status' => 'published',
            'startTimeGreaterThan' => time(),
            'courseIds' => $courseIds
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchLessonCount($conditions),
            10
        );

        $lessons = $this->getCourseService()->searchLessons(
            $conditions,  
            array('startTime', 'ASC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $newCourses = array();

        $courses = ArrayToolkit::index($courses, 'id');

        if (!empty($courses)) {
            foreach ($lessons as $key => &$lesson) {
                $newCourses[$key] = $courses[$lesson['courseId']];
                $newCourses[$key]['lesson'] = $lesson;
            }
        }
        
        return $this->render('TopxiaWebBundle:MyLiveCourse:index.html.twig', array(
            'courses' => $newCourses,
            'paginator' => $paginator
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}