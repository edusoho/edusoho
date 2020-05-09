<?php

namespace AppBundle\Controller;

use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use PhpOffice\PhpWord\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function buildCheckAction(Request $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $fields = $request->request->all();
        $fields['questionTypes'] = explode(',', $fields['types']);
        $fields['range'] = json_decode($fields['range'], true);

        $condition = $this->getExerciseConfig()->getCondition($fields);

        try {
            $result = $this->getAssessmentService()->drawItems($condition['range'], array($condition['section']));
        } catch (\Exception $e) {
            return $this->createJsonResponse(false);
        }

        foreach ($result as $section) {
            if (!empty($section['items']['miss'])) {
                return $this->createJsonResponse(false);
            }
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
