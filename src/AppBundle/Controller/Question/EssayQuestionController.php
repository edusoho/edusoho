<?php

namespace AppBundle\Controller\Question;

use AppBundle\Controller\BaseController;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class EssayQuestionController extends BaseController
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $courseSetId, $questionId)
    {
        $user = $this->getUser();
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $question = $this->getQuestionService()->get($questionId);

        $parentQuestion = array();
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($question['parentId']);
        }

        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSetId);
        $courseTasks = $this->getCourseTaskService()->findTasksByCourseId($question['courseId']);

        return $this->render('question-manage/essay-form.html.twig', array(
            'courseSet' => $courseSet,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
            'type' => $question['type'],
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
        ));
    }

    public function createAction(Request $request, $courseSetId, $type)
    {
        $user = $this->getUser();
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSetId);

        $parentId = $request->query->get('parentId', 0);
        $parentQuestion = $this->getQuestionService()->get($parentId);

        return $this->render('question-manage/essay-form.html.twig', array(
            'courseSet' => $courseSet,
            'parentQuestion' => $parentQuestion,
            'type' => $type,
            'courses' => $manageCourses,
        ));
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
