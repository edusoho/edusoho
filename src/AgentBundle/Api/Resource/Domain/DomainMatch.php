<?php

namespace AgentBundle\Api\Resource\Domain;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseService;

class DomainMatch extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $type = $request->request->get('type', 'course');
        if ('itemBankExercise' == $type) {
            $exercise = $this->getItemBankExerciseService()->tryManageExercise($id);
            $title = $exercise['title'];
        } else {
            $course = $this->getCourseService()->tryManageCourse($id);
            $title = $course['courseSetTitle'];
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
