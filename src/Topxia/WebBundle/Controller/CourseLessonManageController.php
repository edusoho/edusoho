<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseLessonManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$courseItems = $this->getCourseService()->getCourseItems($course['id']);
		return $this->render('TopxiaWebBundle:CourseLessonManage:index.html.twig', array(
			'course' => $course,
			'items' => $courseItems
		));
	}

	public function createAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

	    if($request->getMethod() == 'POST') {
        	$lesson = $request->request->all();
        	$lesson['courseId'] = $course['id'];

        	if ($lesson['media']) {
        		$lesson['media'] = json_decode($lesson['media'], true);
        	}
        	if ($lesson['length']) {
        		$lesson['length'] = $this->textToSeconds($lesson['length']);
        	}
        	$lesson = $this->getCourseService()->createLesson($lesson);

			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
			));
        }

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
		));
	}

	public function editAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

	    if($request->getMethod() == 'POST'){
        	$fields = $request->request->all();
        	if ($fields['media']) {
        		$fields['media'] = json_decode($fields['media'], true);
        	}
        	if ($fields['length']) {
        		$fields['length'] = $this->textToSeconds($fields['length']);
        	}

        	$fields['free'] = empty($fields['free']) ? 0 : 1;
        	$lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);
			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
			));
        }

        $lesson['length'] = $this->secondsToText($lesson['length']);

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
		));
	}

	public function publishAction(Request $request, $courseId, $lessonId)
	{
		$this->getCourseService()->publishLesson($courseId, $lessonId);
		return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
			'course' => $this->getCourseService()->getCourse($courseId),
			'lesson' => $this->getCourseService()->getLesson($courseId, $lessonId),
		));
	}

	public function unpublishAction(Request $request, $courseId, $lessonId)
	{
		$this->getCourseService()->unpublishLesson($courseId, $lessonId);
		return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
			'course' => $this->getCourseService()->getCourse($courseId),
			'lesson' => $this->getCourseService()->getLesson($courseId, $lessonId),
		));
	}

	public function sortAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$this->getCourseService()->sortCourseItems($course['id'], $request->request->get('ids'));
		return $this->createJsonResponse(true);
	}


	public function createLessonQuizItemAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

       
		$quizItems = $this->getQuizService()->findLessonQuizItems($course['id'], $lesson['id']);
		$form = $this->createQuizForm();
		if($request->getMethod() == 'POST'){
	        $form->bind($request);
	        if($form->isValid()){
	        	$lessonQuizItemInfo = $form->getData();

                $choices = $request->request->get("choices");
                $choices = json_encode($choices);                
                $lessonQuizItemInfo['choices'] = $choices;                
	        	$lessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'], $lesson['id'], $lessonQuizItemInfo);
                if(!empty($lessonQuizItem)){
                    $html = $this->renderView('TopxiaWebBundle:CourseLessonManage:lesson-quiz-item.html.twig',
                        array('quizItem'=>$lessonQuizItem));
                        return $this->createJsonResponse(array('action'=>'create','status' => 'ok', 'html' => $html));                
                } else{
                        return $this->createJsonResponse(array('action'=>'create','status' => 'error', 'message'=>'创建测试问题失败'));
                }
            }
	    }

        return $this->render('TopxiaWebBundle:CourseLessonManage:quiz-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'quizItems' => $quizItems,
			'form' => $form->createView(),
		));
	}

    public function deleteLessonQuizItemAction(Request $request, $quizItemId)
    {
        $this->getQuizService()->deleteLessonQuizItem($quizItemId);
        return $this->createJsonResponse(true);
    }

    public function editLessonQuizItemAction(Request $request, $quizItemId)
    {

        $lessonQuizItem = $this->getQuizService()->getLessonQuizItem($quizItemId);
        $lessonQuizItem['choices'] = json_decode($lessonQuizItem['choices']);   
        return $this->createJsonResponse(array('lessonQuizItem' => $lessonQuizItem));
    }

    public function updateLessonQuizItemAction(Request $request, $quizItemId)
    {
        $description = $request->request->get('quiz[description]', null, TRUE);
        $level = $request->request->get('quiz[level]', null, TRUE);
        $answers = $request->request->get('quiz[answers]', null, TRUE);
        $choices = $request->request->get('choices', null, TRUE);
        $fields = array(
            'description'=>$description,
            'level'=>$level,
            'choices'=>$choices,
            'answers'=>$answers);
        $updatedQuizItem = $this->getQuizService()->editLessonQuizItem($quizItemId, $fields);
        $updatedQuizItem['description'] = strip_tags($updatedQuizItem['description']);
        return $this->createJsonResponse(array(
            'action'=>'update',
            'status' => 'ok', 
            'quizItem'=>$updatedQuizItem
            ));
    }

	public function deleteAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$this->getCourseService()->deleteLesson($course['id'], $lessonId);
		return $this->createJsonResponse(true);
	}

	private function createQuizForm(array $data = array())
	{
		return $this->createNamedFormBuilder('quiz', $data)
			->add('level', 'choice', array(
            	'choices' => array(
            		'low' => '入门',
            		'normal' => '中级',
            		'high' => '高级'),
            	'data' => 'normal',
        		'expanded' => true,
        	))
        	->add('description', 'textarea')
            ->add('choices', 'hidden')
            ->add('answers', 'hidden')
            ->getForm();
	}

	private function secondsToText($value)
	{
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;
        return sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
	}

	private function textToSeconds($text)
	{
		if (strpos($text, ':') === false) {
			return 0;
		}
		list($minutes, $seconds) = explode(':', $text, 2);
		return intval($minutes) * 60 + intval($seconds);
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