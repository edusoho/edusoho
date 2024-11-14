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
        if ($itemBankExercise['isMember']) {
            $itemBankExercise['canLearn'] = (int) ($member['canLearn'] && $itemBankExercise['canLearn']);
        }
        if (!empty($member)) {
            $itemBankExercise['access'] = $this->getItemBankExerciseService()->canLearnExercise($id);
        } else {
            $itemBankExercise['access'] = $this->getItemBankExerciseService()->canJoinExercise($id);
        }
        $goodsKey = 'itemBankExercise_'.$itemBankExercise['id'];
        $signRecord = $this->getContractService()->getSignRecordByUserIdAndGoodsKey($this->getCurrentUser()->getId(), $goodsKey);
        $itemBankExercise['isContractSigned'] = empty($signRecord) ? 0 : 1;
        if (0 == $itemBankExercise['isContractSigned']) {
            $member = $this->getItemBankExerciseMemberService()->getExerciseMember($id, $user['id']);
            if ('bind_join' == $member['joinedChannel']) {
                $itemBankExercise['isContractSigned'] = 1;
            }
        }
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

        if (isset($conditions['categoryId'])) {
            if (!empty($conditions['categoryId'])) {
                $conditions['categoryIds'] = $this->getCategoryService()->findAllChildrenIdsByParentId($conditions['categoryId']);
                array_unshift($conditions['categoryIds'], $conditions['categoryId']);
            }
            unset($conditions['categoryId']);
        }
        if (isset($conditions['bindId']) && isset($conditions['bindType'])) {
            $exerciseBinds = $this->getItemBankExerciseService()->findBindExercise($conditions['bindType'], $conditions['bindId']);
            $exerciseIds = array_column($exerciseBinds, 'itemBankExerciseId');
            $conditions['excludeIds'] = $exerciseIds;
            unset($conditions['bindId']);
            unset($conditions['bindType']);
        }
        if (isset($conditions['updatedUser'])) {
            $user = $this->getUserService()->findUserLikeNickname($conditions['updatedUser']);
            $conditions['updatedUsers'] = array_column($user, 'id');

            unset($conditions['updatedUser']);
        }
        if (isset($conditions['updatedUsers']) && empty($conditions['updatedUsers'])) {
            $conditions['updatedUsers'] = [-1];
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
        $userIds = array_values(array_unique(array_column($itemBankExercises, 'updated_user_id')));
        $users = $this->getUserService()->findUsersByIds($userIds);
        foreach ($itemBankExercises as &$itemBankExercise) {
            $itemBankExercise['updatedUser'] = $users[$itemBankExercise['updated_user_id']] ?? null;
        }
        $categoryIds = array_values(array_unique(array_column($itemBankExercises, 'categoryId')));
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        foreach ($itemBankExercises as &$itemBankExercise) {
            $itemBankExercise['category'] = $categories[$itemBankExercise['categoryId']] ?? null;
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

    /**
     * @return \Biz\QuestionBank\Service\CategoryService
     */
    private function getCategoryService()
    {
        return $this->service('QuestionBank:CategoryService');
    }
}
