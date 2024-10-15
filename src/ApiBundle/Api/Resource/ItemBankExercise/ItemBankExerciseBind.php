<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\ItemBankExercise\Service\ExerciseService;

class ItemBankExerciseBind extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $params = $request->request->all();
        $this->getItemBankExerciseService()->bindExercise($params['bindType'], $params['bindId'], $exerciseId);

        return ['success' => true];
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}