<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Contract\Service\ContractService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\User\Service\UserService;

class ItemBankExercise extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($id);
        if (empty($itemBankExercise)) {
            throw ItemBankExerciseException::NOTFOUND_EXERCISE();
        }

        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            $member = $this->getItemBankExerciseMemberService()->getExerciseMember($id, $user['id']);
        }

        $itemBankExercise['isMember'] = !empty($member);
        if (!empty($member)) {
            $itemBankExercise['access'] = $this->getItemBankExerciseService()->canLearnExercise($id);
        } else {
            $itemBankExercise['access'] = $this->getItemBankExerciseService()->canJoinExercise($id);
        }
        $goodsKey = 'itemBankExercise_'.$itemBankExercise['id'];
        $signRecord = $this->getContractService()->getSignRecordByUserIdAndGoodsKey($this->getCurrentUser()->getId(), $goodsKey);
        $itemBankExercise['isContractSigned'] = empty($signRecord) ? 0 : 1;
        $itemBankExercise['contract'] = $this->getContractService()->getRelatedContractByGoodsKey($goodsKey);
        if (empty($itemBankExercise['contract'])) {
            $itemBankExercise['contract'] = [
                'sign' => 'no',
            ];
        } else {
            $itemBankExercise['contract'] = [
                'sign' => $itemBankExercise['contract']['sign'] ? 'required' : 'optional',
                'name' => $itemBankExercise['contract']['contractName'],
                'id' => $itemBankExercise['contract']['contractId'],
                'goodsKey' => $goodsKey,
            ];
        }

        return $itemBankExercise;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = $request->query->all();
        $conditions['status'] = 'published';

        if (isset($conditions['categoryId']) && '0' == $conditions['categoryId']) {
            unset($conditions['categoryId']);
        }
        if (isset($conditions['bindId']) && isset($conditions['bindType'])) {
            $exerciseBinds = $this->getItemBankExerciseService()->findBindExercise($conditions['bindType'], $conditions['bindId']);
            $exerciseIds = array_column($exerciseBinds, 'itemBankExerciseId');
            $conditions['excludeIds'] = $exerciseIds;
        }
        if (isset($conditions['updateUser'])) {
            $user = $this->getUserService()->findUserLikeNickname($conditions['updateUser']);
            $conditions['updatedUsers'] = array_column($user, 'id');
        }

        $sort = $this->getSort($request) ?: ['recommendedSeq' => 'ASC'];

        if (array_key_exists('recommendedSeq', $sort)) {
            $sort = ['recommended' => 'DESC', 'recommendedSeq' => 'ASC', 'updatedTime' => 'DESC'];
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

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
