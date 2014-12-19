<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\StringToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseChapterManageController extends BaseController
{
	public function editAction(Request $request, $courseId, $chapterId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$chapter = $this->getCourseService()->getChapter($courseId, $chapterId);
		if (empty($chapter)) {
			throw $this->createNotFoundException("章节(#{$chapterId})不存在！");
		}

	    if($request->getMethod() == 'POST'){
        	$fields = $request->request->all();
        	$fields['courseId'] = $course['id'];
        	$chapter = $this->getCourseService()->updateChapter($courseId, $chapterId, $fields);
			return $this->render('TopxiaWebBundle:CourseChapterManage:list-item.html.twig', array(
				'course' => $course,
				'chapter' => $chapter,
			));
        }

		return $this->render('TopxiaWebBundle:CourseChapterManage:chapter-modal.html.twig', array(
			'course' => $course,
			'chapter' => $chapter,
            'type' => $chapter['type'],
		));
		
	}


	private function getCourseService(){
		return $this->getServiceKernel()->createService('Custom:Course.CourseService');
	}
}