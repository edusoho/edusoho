<?php

namespace AppBundle\Controller\Question;

use Biz\Question\QuestionException;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Service\QuestionService;
use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

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

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
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
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
