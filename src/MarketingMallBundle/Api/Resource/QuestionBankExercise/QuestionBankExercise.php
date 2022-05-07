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
        $offset = $conditions['offset'] ?? static::DEFAULT_PAGING_OFFSET;
        $limit = $conditions['limit'] ?? static::DEFAULT_PAGING_LIMIT;
        $columns = [
            'id',
            'questionBankId',
            'title',
            'cover',
            'originPrice',
        ];
        $exercise = $this->getExerciseService()->search($conditions, $orderBys, $offset, $limit, $columns);
        $total = $this->getExerciseService()->count($conditions);
        
        return $this->makePagingObject($exercise, $total, $offset, $limit);
    }

    /**
     * @return ExerciseService
     */
    private function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}