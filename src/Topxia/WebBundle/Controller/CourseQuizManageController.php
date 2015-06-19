<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseQuizManageController extends BaseController
{

	public function indexAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

		$items = $this->getQuizService()->findLessonQuizItems($course['id'], $lesson['id']);

        return $this->render('TopxiaWebBundle:CourseQuizManage:quiz-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'quizItems' => $items,
		));
	}

    public function saveItemAction(Request $request, $courseId, $lessonId)
    {
        $item = $request->request->all();
        $item['answers'] = explode('|', $item['answers']);
        
        if (empty($item['id'])) {
            $item['courseId'] = $courseId;
            $item['lessonId'] = $lessonId;
            $item = $this->getQuizService()->createItem($item);
        } else {
            $item = $this->getQuizService()->updateItem($item['id'], $item);
        }

        return $this->createJsonResponse($item);
    }

    public function deleteItemAction(Request $request, $courseId, $itemId)
    {
        $this->getQuizService()->deleteItem($itemId);
        return $this->createJsonResponse(true);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuizService()
    {
        return $this->getServiceKernel()->createService('Course.QuizService');
    }

}