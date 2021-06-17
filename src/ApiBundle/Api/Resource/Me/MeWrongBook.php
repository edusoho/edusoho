<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\WrongBook\Service\WrongQuestionService;

class MeWrongBook extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookFilter", mode="public")
     */
    public function search(ApiRequest $request)
    {
        $defaultWrongPools = [
            'course' => 0,
            'classroom' => 0,
            'exercise' => 0,
        ];
        $wrongPools = $this->getWrongQuestionService()->getWrongBookPoolByFieldsGroupByTargetType(['user_id' => $this->getCurrentUser()->getId()]);
        $wrongPools = empty($wrongPools) ? $defaultWrongPools : ArrayToolkit::index($wrongPools, 'target_type');
        $wrongPools = array_merge($defaultWrongPools, $wrongPools);

        return $wrongPools;
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }
}
