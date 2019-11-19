<?php

namespace AppBundle\Controller\Question;

use Biz\QuestionBank\QuestionBankException;
use Symfony\Component\HttpFoundation\Request;

class SingleChoiceQuestionController extends BaseQuestionController
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $courseSetId, $questionId)
    {
        list($courseSet, $question) = $this->tryGetCourseSetAndQuestion($courseSetId, $questionId);
        $user = $this->getUser();

        $parentQuestion = array();
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($question['parentId']);
        }

        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSetId);
        $courseTasks = $this->getTaskService()->findTasksByCourseId($question['courseId']);

        return $this->render('question-manage/single-choice-form.html.twig', array(
            'courseSet' => $courseSet,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
            'type' => $question['type'],
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
            'request' => $request,
        ));
    }

    public function createAction(Request $request, $questionBankId, $type)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($questionBankId)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($questionBankId);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $parentId = $request->query->get('parentId', 0);
        $parentQuestion = $this->getQuestionService()->get($parentId);

        return $this->render('question-manage/single-choice-form.html.twig', array(
            'parentQuestion' => $parentQuestion,
            'questionBank' => $questionBank,
            'type' => $type,
        ));
    }
}
