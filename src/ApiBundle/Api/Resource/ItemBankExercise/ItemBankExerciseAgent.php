<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use AgentBundle\Biz\AgentConfig\Exception\AgentConfigException;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\AI\Service\AIService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Service\ExerciseService;

class ItemBankExerciseAgent extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $this->getItemBankExerciseService()->tryManageExercise($exerciseId);
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, ['isActive', 'domainId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->checkDomain($params['domainId']);
        $this->getItemBankExerciseService()->update($exerciseId, [
            'isAgentActive' => $params['isActive'],
            'agentDomainId' => $params['domainId'],
        ]);

        return ['ok' => true];
    }

    private function checkDomain($domainId)
    {
        $domains = $this->getAIService()->findDomains('vt');
        $domains = array_column($domains, null, 'id');
        if (empty($domains[$domainId])) {
            throw AgentConfigException::UNKNOWN_DOMAIN();
        }
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
        return $this->service('AI:AIService');
    }
}
