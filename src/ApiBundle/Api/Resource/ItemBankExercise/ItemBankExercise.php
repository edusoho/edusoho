<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ItemBankExercise extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $member = $this->getItemBankExerciseMemberService()->getExerciseMember($id, $user['id']);
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($id);

        return $itemBankExercise;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = $request->query->all();

        $sort = $this->getSort($request);
        if (array_key_exists('recommendedSeq', $sort)) {
            $sort = array_merge($sort, ['recommendedTime' => 'DESC', 'id' => 'DESC']);
            $itemBankExercises = $this->getItemBankExerciseService()->search($conditions, $sort, $offset, $limit);
        } elseif (array_key_exists('studentNum', $sort) && array_key_exists('lastDays', $conditions)) {
            $itemBankExercises = $this->getItemBankExerciseService()->searchOrderByStudentNumAndLastDays($conditions, $conditions['lastDays'], $offset, $limit);
        } elseif (array_key_exists('rating', $sort) && array_key_exists('lastDays', $conditions)) {
            $itemBankExercises = $this->getItemBankExerciseService()->searchOrderByRatingAndLastDays($conditions, $conditions['lastDays'], $offset, $limit);
        } else {
            $itemBankExercises = $this->getItemBankExerciseService()->search($conditions, $sort, $offset, $limit);
        }

        return $this->makePagingObject($itemBankExercises, $this->getItemBankExerciseService()->count($conditions), $offset, $limit);
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
