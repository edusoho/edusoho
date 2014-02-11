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

        $conditions = array();
        $conditions['target'] = "course-{$course['id']}";
        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpapersCount($conditions),
            10
        );


        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' ,'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($testpapers, 'updatedUserId')); 
        
        return $this->render('TopxiaWebBundle:CourseTestpaperManage:index.html.twig', array(
            'course' => $course,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,

        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $testPaper = $request->query->all();

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['target'] = "course-{$course['id']}";
            $fields['pattern'] = 'QuestionType';
            $testpaper = $this->getTestpaperService()->createTestpaper($fields);
            var_dump($testpaper);exit();
            return $this->redirect($this->generateUrl('course_manage_testpaper_create_two',$testPaper));
        }

        return $this->render('TopxiaWebBundle:CourseTestpaperManage:create.html.twig', array(
            'course'    => $course,
            'ranges' => $this->getQuestionRanges($course),
        ));
    }

    private function getQuestionRanges($course)
    {
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $ranges = array();
        foreach ($lessons as  $lesson) {
            if ($lesson['type'] == 'testpaper') {
                continue;
            }
            $ranges["lesson-{$lesson['id']}"] = "课时{$lesson['number']}： {$lesson['title']}";
        }

        return $ranges;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}