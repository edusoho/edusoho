<?php

namespace AppBundle\Controller;

use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function buildCheckAction(Request $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $fields = $request->request->all();
        $fields['questionTypes'] = explode(',', $fields['types']);
        $range = json_decode($fields['range'], true);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($range['bankId']);

        $conditions = [
            'bank_id' => empty($questionBank['itemBankId']) ? 0 : $questionBank['itemBankId'],
            'types' => $fields['questionTypes'],
        ];

        if (!empty($range['categoryIds'])) {
            $conditions['category_ids'] = explode(',', $range['categoryIds']);
        }

        if (!empty($fields['difficulty'])) {
            $conditions['difficulty'] = $fields['difficulty'];
        }

        $count = $this->getItemService()->countItems($conditions);
        if ($count < $fields['itemCount']) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse(true);
    }

    protected function getExerciseConfig()
    {
        $biz = $this->getBiz();

        return $biz['activity_type.exercise'];
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
