<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ItemBankExerciseMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId)
    {
        $conditions['role'] = 'student';
        $conditions['exerciseId'] = $exerciseId;
        $conditions['locked'] = 0;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        $total = $this->getItemBankExerciseMemberService()->count($conditions);

        $this->getOCUtil()->multiple($members, ['userId']);

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $exerciseId)
    {
        $member = $this->getItemBankExerciseMemberService()->getExerciseMember($exerciseId, $this->getCurrentUser()->getId());

        if (!$member) {
            $member = $this->getItemBankExerciseService()->freeJoinExercise($exerciseId);
        }

        $this->getOCUtil()->single($member, ['userId']);

        return $member;
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}
