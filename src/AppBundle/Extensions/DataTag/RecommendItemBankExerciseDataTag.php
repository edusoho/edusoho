<?php

namespace AppBundle\Extensions\DataTag;

use Biz\ItemBankExercise\Service\ExerciseService;

class RecommendItemBankExerciseDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取推荐题库练习列表.
     *
     * 可传入的参数：
     *   count    必需 题库练习数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 题库练习列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = [
            'status' => 'published',
            'recommended' => 1,
        ];

        $itemBankExercises = $this->getItemBankExerciseService()->search(
            $conditions,
            ['recommendedSeq' => 'asc', 'updatedTime' => 'desc'],
            0,
            $arguments['count']
        );

        $itemBankExerciseCount = count($itemBankExercises);

        if ($itemBankExerciseCount < $arguments['count'] && empty($arguments['notFill'])) {
            $conditions['recommended'] = 0;
            $itemBankExercisesTemp = $this->getItemBankExerciseService()->search(
                $conditions,
                ['updatedTime' => 'desc'],
                0,
                $arguments['count'] - $itemBankExerciseCount
            );
            $itemBankExercises = array_merge($itemBankExercises, $itemBankExercisesTemp);
        }

        return $itemBankExercises;
    }

    protected function checkCount(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException('count参数缺失');
        }

        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException('count参数超出最大取值范围');
        }
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
