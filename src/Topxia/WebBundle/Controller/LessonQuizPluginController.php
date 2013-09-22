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
                'alreadyQuiz'=>$preparedData['alreadyQuiz']));
        }
    }

    public function startAction(Request $request, $courseId, $lessonId)
    {
       
        $preparedData = $this->prepareForStart($courseId, $lessonId);
        if(!empty($preparedData['alreadyQuiz'])){
            $this->getQuizService()->deleteQuiz($preparedData['alreadyQuiz']['id']);
        }
        return $this->render('TopxiaWebBundle:LessonQuizPlugin:lesson-quiz-page.html.twig', array(
            'quizItems'=>$preparedData['quizItems'],
            'lessonQuiz'=>$preparedData['lessonQuiz'],
            'alreadyQuiz'=>$preparedData['alreadyQuiz']));
    }

    public function checkResultAction(Request $request, $quizId)
    {
        $result = $this->getQuizService()->submitQuizResult($quizId); 
        return $this->render('TopxiaWebBundle:LessonQuizPlugin:check-result.html.twig', $result);
    }

    public function postItemAction(Request $request, $quizId, $quizItemId)
    {
        $answers = $request->request->get("answer");
        if (is_null($answers)) {
            $answers = array();
        } else {
            $answers = !is_array($answers) ? array($answers) : $answers;
        }

        $result = $this->getQuizService()->answerQuizItem($quizId, $quizItemId, $answers);

        return $this->createJsonResponse($result);
    }

    private function prepareForStart($courseId, $lessonId)
    {
        $quizItems = array();
        $lessonQuiz = array();
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        $quizItemIds = $this->getQuizService()->findLessonQuizItemIds($course['id'], $lesson['id']);
        $alreadyQuiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $lesson['id'], $this->getCurrentUser()->id);
        if($quizItemIds){
            $quizItemIds = ArrayToolkit::column($quizItemIds, 'id');
            $lessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $lesson['id'], $quizItemIds);
            $quizItems = $this->getQuizService()->findQuizItemsInLessonQuiz($lessonQuiz['id']);
        }
        return array('alreadyQuiz'=>$alreadyQuiz, 'quizItems'=>$quizItems, 'lessonQuiz'=>$lessonQuiz);
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