<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseChapterManageController extends BaseController
{

	public function createAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
       		 $type = $request->query->get('type');
        		$parentId = $request->query->get('parentId');
       		 $type = in_array($type, array('chapter', 'unit')) ? $type : 'chapter';
	    if($request->getMethod() == 'POST'){
        	$chapter = $request->request->all();
        	$chapter['courseId'] = $course['id'];
        	$chapter = $this->getCourseService()->createChapter($chapter);
			return $this->render('TopxiaWebBundle:CourseChapterManage:list-item.html.twig', array(
				'course' => $course,
				'chapter' => $chapter,
			));
        }

		return $this->render('TopxiaWebBundle:CourseChapterManage:chapter-modal.html.twig', array(
			'course' => $course,
            'type' => $type,
            'parentId' => $parentId
		));
	}

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

	public function deleteAction(Request $request, $courseId, $chapterId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$this->getCourseService()->deleteChapter($course['id'], $chapterId);

		return $this->createJsonResponse(true);
	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}