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

    public function search(ApiRequest $request, $exerciseId)
    {
        $conditions = $request->query->all();

        return $this->getItemBankExerciseService()->findBindExercise($conditions['bindType'], $conditions['bindId']);
    }

    public function get(ApiRequest $request)
    {
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}
