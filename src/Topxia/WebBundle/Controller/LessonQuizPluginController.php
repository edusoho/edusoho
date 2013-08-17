<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class LessonQuizPluginController extends BaseController
{
    public function initAction (Request $request, $courseId, $lessonId)
    {

        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        $quiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $lesson['id'], $this->getCurrentUser()->id);
        
        if($quiz){   
            return $this->render('TopxiaWebBundle:LessonQuizPlugin:welcome-already-quiz.html.twig', array('quiz'=>$quiz));
        } else {
            $preparedData = $this->prepareForStart($courseId, $lessonId);
            return $this->render('TopxiaWebBundle:LessonQuizPlugin:lesson-quiz-page.html.twig', array(
                'quizItems'=>$preparedData['quizItems'],
                'lessonQuiz'=>$preparedData['lessonQuiz'],
                'quiz'=>$preparedData['quiz']));
        }
    }

    public function startAction(Request $request, $courseId, $lessonId)
    {
       
        $preparedData = $this->prepareForStart($courseId, $lessonId);
        if(!empty($preparedData['quiz'])){
            $this->getQuizService()->deleteQuiz($preparedData['quiz']['id']);
        }

        return $this->render('TopxiaWebBundle:LessonQuizPlugin:lesson-quiz-page.html.twig', array(
            'quizItems'=>$preparedData['quizItems'],
            'lessonQuiz'=>$preparedData['lessonQuiz'],
            'quiz'=>$preparedData['quiz']));
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

    private function prepareForStart($courseId, $lessonId)
    {
        $quizItems = array();
        $lessonQuiz = array();
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        $quizItemIds = $this->getQuizService()->findLessonQuizItemIds($course['id'], $lesson['id']);
        $quiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $lesson['id'], $this->getCurrentUser()->id);
        if($quizItemIds){
            $quizItemIds = ArrayToolkit::column($quizItemIds, 'id');
            $lessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $lesson['id'], $quizItemIds);
            $quizItems = $this->getQuizService()->findQuizItemsInLessonQuiz($lessonQuiz['id']);
        }
        return array('quiz'=>$quiz, 'quizItems'=>$quizItems, 'lessonQuiz'=>$lessonQuiz);
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