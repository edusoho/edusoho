<?php

namespace MarketingMallBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\ApiRequest;
use Biz\ItemBankExercise\Service\ExerciseService;
use MarketingMallBundle\Api\Resource\BaseResource;

class QuestionBankExercise extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $orderBys = $this->getSortByStr($conditions['sort'] ?? '');
        $start = $conditions['offset'] ?? static::DEFAULT_PAGING_OFFSET;
        $limit = $conditions['limit'] ?? static::DEFAULT_PAGING_LIMIT;
        $columns = [
            'id',
            'questionBankId',
            'title',
            'cover',
            'originPrice',
        ];

        return $this->getExerciseService()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    /**
     * @return ExerciseService
     */
    private function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}