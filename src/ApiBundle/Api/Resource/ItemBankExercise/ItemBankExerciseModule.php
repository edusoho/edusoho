<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ItemBankExerciseModule extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId)
    {
        return $this->getItemBankExerciseModuleService()->findByExerciseId($exerciseId);
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }
}
