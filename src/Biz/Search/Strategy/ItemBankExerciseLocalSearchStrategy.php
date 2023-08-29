<?php

namespace Biz\Search\Strategy;

use Biz\ItemBankExercise\Service\ExerciseService;

class ItemBankExerciseLocalSearchStrategy implements LocalSearchStrategy
{
    use LocalSearchStrategyTrait;

    public function buildSearchConditions($keyword, $filter)
    {
        $this->conditions = [
            'status' => 'published',
            'title' => $keyword,
        ];

        if ('free' == $filter) {
            $this->conditions['price'] = '0.00';
        }
    }

    public function count()
    {
        return $this->getItemBankExerciseService()->count($this->conditions);
    }

    public function search($start, $limit)
    {
        return $this->getItemBankExerciseService()->search(
            $this->conditions,
            ['recommended' => 'desc', 'recommendedSeq' => 'asc', 'updatedTime' => 'desc'],
            $start,
            $limit
        );
    }

    /**
     * @return ExerciseService
     */
    private function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }
}
