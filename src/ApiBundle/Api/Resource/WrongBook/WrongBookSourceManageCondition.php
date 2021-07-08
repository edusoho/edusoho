<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\WrongBook\Service\WrongQuestionService;

class WrongBookSourceManageCondition extends AbstractResource
{
    public function search(ApiRequest $request, $type, $targetId)
    {
        $conditions = $request->query->all();
        $this->getWrongQuestionService()->
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->biz->service('WrongBook:WrongQuestionService');
    }
}
