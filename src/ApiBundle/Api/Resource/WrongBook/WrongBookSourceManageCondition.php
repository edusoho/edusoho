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
        $poolName = 'wrong_question.'.$type.'_pool';

        return $this->biz[$poolName]->buildTargetConditions($targetId, $conditions);
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->biz->service('WrongBook:WrongQuestionService');
    }
}
