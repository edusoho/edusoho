<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\WrongBook\Service\WrongQuestionService;

class MeWrongBook extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookFilter", mode="authenticated")
     */
    public function search(ApiRequest $request)
    {
        $wrongPools = $this->getWrongQuestionService()->getWrongBookPoolByFieldsGroupByTargetType(['user_id' => $this->getCurrentUser()->getId()]);
        $wrongPools = empty($wrongPools) ? [] : ArrayToolkit::index($wrongPools, 'target_type');
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
