<?php

namespace MarketingMallBundle\Api\Resource\QuestionBankExercise;

use ApiBundle\Api\ApiRequest;
use Biz\ItemBankExercise\Service\ExerciseService;
use MarketingMallBundle\Api\Resource\BaseResource;

class QuestionBankExercise extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions['titleLike'])) {
            $conditions['title'] = $conditions['titleLike'];
            unset($conditions['titleLike']);
        }
        $orderBys = ['createdTime' => 'DESC'];
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