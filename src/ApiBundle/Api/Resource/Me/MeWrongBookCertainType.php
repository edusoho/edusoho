<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\WrongBook\Service\WrongQuestionService;

class MeWrongBookCertainType extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookCertainTypeFilter", mode="public")
     */
    public function search(ApiRequest $request, $type)
    {
        $conditions = $request->query->all();
        $conditions['user_id'] = $this->getCurrentUser()->getId();
        $conditions['target_type'] = $type;
        $conditions['item_num_GT'] = 0;
        if ('exercise' == $type) {
            $conditions['target_ids'] = ArrayToolkit::column($this->getExerciseMemberService()->findByUserIdAndRole($this->getCurrentUser()->getId(), 'student'), 'questionBankId');
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $wrongBookPools = $this->getWrongQuestionService()->searchWrongBookPool(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );
        $total = $this->getWrongQuestionService()->countWrongBookPool($conditions);

        $relationField = 'target_id';
        if ('exercise' == $type) {
            $type = 'item_bank_exercise';
            $wrongBookPools = $this->bankExchangeExercise($wrongBookPools);
            $relationField = 'exercise_id';
        } elseif ('course' == $type) {
            $type = 'courseSet';
        }

        $this->getOCUtil()->multiple($wrongBookPools, [$relationField], $type, 'target_data');

        return $this->makePagingObject($wrongBookPools, $total, $offset, $limit);
    }

    protected function bankExchangeExercise($wrongBookPools)
    {
        $bankExercise = $this->getExerciseService()->findByQuestionBankIds(ArrayToolkit::column($wrongBookPools, 'target_id'));
        $bankExercise = ArrayToolkit::index($bankExercise, 'questionBankId');
        foreach ($wrongBookPools as &$pool) {
            $pool['exercise_id'] = $bankExercise[$pool['target_id']]['id'];
        }

        return $wrongBookPools;
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
