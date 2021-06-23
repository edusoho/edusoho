<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\WrongBook\Service\WrongQuestionService;

class MeWrongBookCertainType extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookCertainTypeFilter", mode="public")
     */
    public function search(ApiRequest $request, $type)
    {
        $conditions = $request->query->all();
        $conditions['user_id'] = $this->getCurrentUser()->getId();
        $conditions['target_type'] = $type;

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $wrongBookPools = $this->service('WrongBook:WrongQuestionService')->searchWrongBookPool(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );
        $total = $this->service('WrongBook:WrongQuestionService')->countWrongBookPool($conditions);
        if ('exercise' == $type) {
            $type = 'item_bank_exercise';
        } elseif ('course' == $type) {
            $type = 'courseSet';
        }

        $this->getOCUtil()->multiple($wrongBookPools, ['target_id'], $type, 'target_data');

        return $this->makePagingObject($wrongBookPools, $total, $offset, $limit);
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }
}
