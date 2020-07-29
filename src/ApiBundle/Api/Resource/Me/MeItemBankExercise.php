<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class MeItemBankExercise extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $conditions = ['role' => 'student', 'userId' => $user['id']];
        $total = $this->getItemBankExerciseMemberService()->count($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['updatedTime' => 'DESC'],
            $offset,
            $limit
        );

        $itemBankExercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($members, 'exerciseId'));
        foreach ($members as &$member) {
            $member['itemBankExercise'] = empty($itemBankExercises[$member['exerciseId']]) ? (object) [] : $itemBankExercises[$member['exerciseId']];
        }

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
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
