<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBind;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\ItemBankExercise\Service\ExerciseService;

class ItemBankExerciseBind extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $this->getItemBankExerciseService()->bindExercise($params['bindType'], $params['bindId'], $params['exerciseIds']);

        return ['success' => true];
    }

    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $bindExercises = $this->getItemBankExerciseService()->findBindExercise($conditions['bindType'], $conditions['bindId']);
        $exerciseIds = array_values(array_unique(array_column($bindExercises, 'itemBankExerciseId')));
        $itemBankExercises = $this->getItemBankExerciseService()->findByIds($exerciseIds);
        foreach ($bindExercises as &$bindExercise) {
            $bindExercise['itemBankExercise'] = $itemBankExercises[$bindExercise['itemBankExerciseId']] ?? null;
        }

        return $bindExercises;
    }

    public function remove(ApiRequest $request)
    {
        $params = $request->request->all();
        $this->getItemBankExerciseService()->removeBindExercise($params['bindType'], $params['bindId'], $params['exerciseId']);

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
