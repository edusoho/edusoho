<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class LessonQuizPluginController extends BaseController
{
    public function initAction (Request $request, $courseId, $lessonId)
    {

        $currentUser = $this->getCurrentUser(); 
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        $quiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $lesson['id'], $currentUser['id']);

        if(!empty($quiz)){   
            return $this->render('TopxiaWebBundle:LessonQuizPlugin:welcome-already-quiz.html.twig', array(
                'quiz'=>$quiz,
                'course'=>$course,
                'lesson'=>$lesson));
        } else {
            $quizItemIds = $this->getQuizService()->findLessonQuizItemIds($course['id'], $lesson['id']);
            $quizItems = array();
            $lessonQuiz = array();
            if(!empty($quizItemIds)){
                $quizItemIds = ArrayToolkit::column($quizItemIds, 'id');
                $lessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $lesson['id'], $quizItemIds);
                $quizItems = $this->getQuizService()->findQuizItemsInLessonQuiz($lessonQuiz['id']);
            }
            return $this->render('TopxiaWebBundle:LessonQuizPlugin:lesson-quiz-page.html.twig', array(
            'quizItems'=>$quizItems,
            'lessonQuiz'=>$lessonQuiz,
            'quiz'=>$quiz,
            'course'=>$course,
            'lesson'=>$lesson));
        }
    }

    public function startAction(Request $request, $courseId, $lessonId)
    {
        $currentUser = $this->getCurrentUser(); 
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        $quizItemIds = $this->getQuizService()->findLessonQuizItemIds($course['id'], $lesson['id']);
        $quizItems = array();
        $lessonQuiz = array();
        $quiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $lesson['id'], $currentUser['id']);
        if(!empty($quizItemIds)){
            $quizItemIds = ArrayToolkit::column($quizItemIds, 'id');
            $lessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $lesson['id'], $quizItemIds);
            $quizItems = $this->getQuizService()->findQuizItemsInLessonQuiz($lessonQuiz['id']);
        }
        if(!empty($quiz)){
            $this->getQuizService()->deleteQuiz($quiz['id']);
        }

        return $this->render('TopxiaWebBundle:LessonQuizPlugin:lesson-quiz-page.html.twig', array(
            'quizItems'=>$quizItems,
            'lessonQuiz'=>$lessonQuiz,
            'quiz'=>$quiz,
            'course'=>$course,
            'lesson'=>$lesson));
    }

    public function checkResultAction(Request $request, $quizId)
    {
        $checkResultInfo = $this->getQuizService()->checkUserLessonQuizResult($quizId); 
        $html = $this->renderView('TopxiaWebBundle:LessonQuizPlugin:check-result.html.twig', array(
            'score'=>$checkResultInfo['score'],
            'correctCount'=>$checkResultInfo['correctCount'],
            'wrongCount'=>$checkResultInfo['wrongCount']));
        return $this->createJsonResponse(array('html'=>$html));
    }

    public function postItemAction(Request $request, $quizId, $quizItemId)
    {
        $quizItem = $this->getQuizService()->getQuizItem($quizItemId);
        $currentChoice = $request->request->get("currentChoice");        
        $currentChoice = substr($currentChoice, 0, strlen($currentChoice)-1);
        $isError = $this->getQuizService()->answerLessonQuizItem($quizId, $quizItem['id'], $currentChoice);
        return $this->createJsonResponse(array('action' => $isError, 'answers'=>$quizItem['answers']));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getQuizService()
    {
        return $this->getServiceKernel()->createService('Course.QuizService');
    }

}