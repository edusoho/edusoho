<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\ItemBankExercise\Service\ExerciseModuleService;

class ItemBankExerciseModule extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($exerciseId);
        if (empty($itemBankExercise)) {
            return [];
        }

        $types = [];
        if (1 == $itemBankExercise['assessmentEnable']) {
            $types[] = ExerciseModuleService::TYPE_ASSESSMENT;
        }

        if (1 == $itemBankExercise['chapterEnable']) {
            $types[] = ExerciseModuleService::TYPE_CHAPTER;
        }

        return $this->getItemBankExerciseModuleService()->search(['exerciseId' => $exerciseId, 'types' => $types], [], 0, PHP_INT_MAX);
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}
