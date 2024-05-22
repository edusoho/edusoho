<?php

namespace ApiBundle\Api\Resource\Ai;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;

class AiStopGenerate extends AbstractResource
{
    public function add(ApiRequest $request, $type)
    {
        $params = $request->request->all();
        if ('question_analysis' == $type) {
            $this->getAIService()->stopGeneratingAnswer('', $params['messageId'], $params['taskId']);
        }

        return ['ok' => true];
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->service('AI:AIService');
    }
}
