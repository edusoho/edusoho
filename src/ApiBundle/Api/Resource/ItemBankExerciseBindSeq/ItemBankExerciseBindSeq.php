<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBindSeq;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\ItemBankExercise\Service\ExerciseService;

class ItemBankExerciseBindSeq extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $ids = $params['ids'];
        $itemBankExercises = $this->getItemBankExerciseService()->findBindExercise($params['bindType'], $params['bindId']);

        foreach ($ids as $index => $id) {
            foreach ($itemBankExercises as $itemBankExercise) {
                if ($itemBankExercise['id'] == $id) {
                    $itemBankExercise['seq'] = $index + 1;
                }
            }
        }
        $this->getItemBankExerciseService()->updateBindExercise($itemBankExercises);

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
