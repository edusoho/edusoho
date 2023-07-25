<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;

class ItemBankExercise extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($id);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);
        if (empty($questionBank['itemBank'])) {
            throw QuestionBankException::NOT_FOUND_BANK();
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

        $sort = $this->getSort($request);
        if (array_key_exists('recommendedSeq', $sort)) {
            $sort = ['recommended' => 'DESC', 'recommendedSeq' => 'ASC', 'createdTime' => 'DESC'];
            $itemBankExercises = $this->getItemBankExerciseService()->search($conditions, $sort, $offset, $limit);
        } elseif (array_key_exists('studentNum', $sort) && array_key_exists('lastDays', $conditions)) {
            $itemBankExercises = $this->getItemBankExerciseService()->searchOrderByStudentNumAndLastDays($conditions, $conditions['lastDays'], $offset, $limit);
        } elseif (array_key_exists('rating', $sort) && array_key_exists('lastDays', $conditions)) {
            $itemBankExercises = $this->getItemBankExerciseService()->searchOrderByRatingAndLastDays($conditions, $conditions['lastDays'], $offset, $limit);
        } else {
            $itemBankExercises = $this->getItemBankExerciseService()->search($conditions, $sort, $offset, $limit);
        }

        $count = $this->getItemBankExerciseService()->count($conditions);

        foreach ($itemBankExercises as &$itemBankExercise) {
            $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);
            if (empty($questionBank['itemBank'])) {
                $itemBankExercise = [];
                --$count;
            }
        }

        return $this->makePagingObject($itemBankExercises, $count, $offset, $limit);
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
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }
}
