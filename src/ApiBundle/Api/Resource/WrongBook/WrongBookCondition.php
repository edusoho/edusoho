<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;

class WrongBookCondition extends AbstractResource
{
    public function search(ApiRequest $request, $poolId)
    {
        $pool = $this->getWrongQuestionService()->getPool($poolId);
        if (empty($pool)) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_NOT_EXIST();
        }

        $poolName = 'wrong_question.'.$pool['target_type'].'_pool';

        return $this->biz[$poolName]->prepareConditions($pool['target_id'], $request->query->all());
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }
}
