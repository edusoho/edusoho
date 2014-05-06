<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class LiveCourseController extends BaseController
{
	public function exploreAction(Request $request)
	{
        if (!$this->setting('course.live_course_enabled')) {
            return $this->createMessageResponse('info', '直播频道已关闭');
        }

	}

    public function listAction(Request $request, $category)
    {
        $now = time();

        $today = date("Y-m-d");
        $today = strtotime("$today");

        $tomorrow = strtotime("$today tomorrow");

        $nextweek = strtotime("$today next week");

        if (!empty($category)) {
            if (ctype_digit((string) $category)) {
                $category = $this->getCategoryService()->getCategory($category);
            } else {
                $category = $this->getCategoryService()->getCategoryByCode($category);
            }

            if (empty($category)) {
                throw $this->createNotFoundException();
            }
        } else {
            $category = array('id' => null);
        }

        $group = $this->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->getCategoryTree($group['id']);
        }

        $date = $request->query->get('date', 'today');

        $conditions = array(
            'status' => 'published',
            'categoryId' => $category['id'],
            'isLive' => '1'
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            , 10
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions, 'lastest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $popularCourses = $this->getCourseService()->searchCourses( array( 'status' => 'published', 'isLive' => '1' ), 'studentNum',0,10 );

        return $this->render('TopxiaWebBundle:LiveCourse:list.html.twig',array(
            'now' => $now,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'nextweek' => $nextweek,
            'date' => $date,
            'category' => $category,
            'categories' => $categories,
            'paginator' => $paginator,
            'courses' => $courses,
            'popularCourses' => $popularCourses
        ));
    }

  	public function createAction(Request $request)
    {
        if($request->getMethod() == 'POST') {
            $data = $request->query->all();
            var_dump($data);
            exit();
        }
            
        return $this->render('TopxiaWebBundle:LiveCourse:live-lesson-modal.html.twig',array(
        	
        ));
    }


    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}