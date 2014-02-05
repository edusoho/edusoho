<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseQuestionManageController extends BaseController
{

    public function indexAction(Request $request, $courseId)
    {
        $course   = $this->getCourseService()->tryManageCourse($courseId);
        
        $conditions    = $request->query->all();

        $questionCount = $this->getQuestionService()->searchQuestionsCount($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $questionCount,
            10
        );

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            array('createdTime' ,'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        return $this->render('TopxiaWebBundle:CourseQuestionManage:index.html.twig', array(
            'course' => $course,
            'questions' => $questions,
            'users' => $users,
            'targets' => $targets,
            'paginator' => $paginator,
            'parentId' => 0,
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $question = array(
            'id' => 0,
            'type' => $type,
        );

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$type}.html.twig", array(
            'course' => $course,
            'question' => $question,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}