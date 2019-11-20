<?php

namespace AppBundle\Controller\Question;

use Biz\Question\QuestionException;
use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Service\QuestionService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class BaseQuestionController extends BaseController
{
    protected function tryGetCourseSetAndQuestion($courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $question = $this->getQuestionService()->get($questionId);

        if ($question['courseSetId'] != $courseSetId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        return array($courseSet, $question);
    }

    protected function baseCreateAction(Request $request, $questionBankId, $type, $view)
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

        return $this->render($view, array(
            'questionBank' => $questionBank,
            'parentQuestion' => $parentQuestion,
            'type' => $type,
            'categoryTree' => $this->getQuestionCategoryService()->getCategoryTree($questionBankId),
        ));
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
