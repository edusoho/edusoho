<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
class CourseSearchController extends BaseController
{	
	//拉取所有的课程，直接到选课页面
	public function indexAction(Request $request)
	{
	// $conditions = $request->query->all();
	// $count = $this->getCustomCourseSearcheService()->searchCourseCount($conditions);

	// $paginator = new Paginator($this->get('request'), $count, 20);
	// $courses = $this->getCustomCourseSearcheService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

	// return $this->render('TopxiaAdminBundle:Course:index.html.twig', array(
	//     'conditions' => $conditions,
	//     'courses' => $courses ,
	//     'paginator' => $paginator
	// ));

	$conditions  = array('categoryId' => 2 );
	$courses = $this->getCustomCourseSearcheService()->searchCourses($conditions,'latest',0,100);
	var_dump($courses);
	exit();

	}
	private function getCustomCourseSearcheService(){
		return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
	}

}