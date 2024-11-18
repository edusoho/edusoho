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
        $bindExercises = $this->getItemBankExerciseService()->findBindExercise($params['bindType'], $params['bindId']);

        foreach ($ids as $index => $id) {
            foreach ($bindExercises as &$bindExercise) {
                if ($bindExercise['id'] == $id) {
                    $bindExercise['seq'] = $index + 1;
                }
            }
        }
        $this->getItemBankExerciseService()->updateBindExercise($bindExercises);

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
