<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseQuizManageController extends BaseController
{

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
                    $html = $this->renderView('TopxiaWebBundle:CourseQuizManage:lesson-quiz-item.html.twig',
                        array('quizItem'=>$lessonQuizItem));
                        return $this->createJsonResponse(array('action'=>'create','status' => 'ok', 'html' => $html));                
                } else{
                        return $this->createJsonResponse(array('action'=>'create','status' => 'error', 'message'=>'创建测试问题失败'));
                }
            }
	    }

        return $this->render('TopxiaWebBundle:CourseQuizManage:quiz-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'quizItems' => $quizItems,
			'form' => $form->createView(),
		));
	}

    public function deleteLessonQuizItemAction(Request $request, $quizItemId)
    {
        $this->getQuizService()->deleteQuizItem($quizItemId);
        return $this->createJsonResponse(true);
    }

    public function editLessonQuizItemAction(Request $request, $quizItemId)
    {

        $lessonQuizItem = $this->getQuizService()->getQuizItem($quizItemId);
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

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuizService()
    {
        return $this->getServiceKernel()->createService('Course.QuizService');
    }

}