<?php

namespace AgentBundle\Api\Resource\DomainMatch;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseService;

class DomainMatch extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['courseId']) && empty($params['exerciseId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if (!empty($params['courseId'])) {
            $course = $this->getCourseService()->tryManageCourse($params['courseId']);
            $title = $course['courseSetTitle'];
        } else {
            $exercise = $this->getItemBankExerciseService()->tryManageExercise($params['exerciseId']);
            $title = $exercise['title'];
        }
        if (!$this->getAIService()->isAgentEnable()) {
            return [
                'id' => '',
            ];
        }
        $domains = $this->getAIService()->findDomains('vt');
        $result = $this->getAIService()->runWorkflow('domain.match.vt', [
            'title' => $title,
            'domains' => $domains,
        ]);

        return [
            'id' => $result['outputs']['id'] ?? '',
        ];
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ExerciseService
     */
    private function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
