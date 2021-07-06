<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;

class WrongBookBankExercise extends AbstractResource
{
    public function search(ApiRequest $request, $poolId)
    {
        $pool = $this->getWrongQuestionService()->getPool($poolId);

        if (empty($pool)) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_NOT_EXIST();
        }
        $bankExercise = $this->getItemBankExerciseService()->getByQuestionBankId($pool['target_id']);
        $exerciseModule = $this->getExerciseModuleService()->findByExerciseId($bankExercise['id']);
        $types = ArrayToolkit::column($exerciseModule, 'type');

        $bankPool = $this->biz['wrong_question.exercise_pool'];
        $bankExerciseModule = [];
        $exerciseSource = $this->bankExerciseSourceConstant();

        foreach ($types as $type) {
            $condition['exerciseMediaType'] = $type = 'assessment' === $type ? 'testpaper' : 'chapter';
            $sceneId = $bankPool->prepareSceneIds($poolId, $condition);
            $typeCount = $this->getWrongQuestionService()->countWrongQuestion(['answer_scene_id' => $sceneId]);
            $bankExerciseModule[$type] = [
                'type' => $type,
                'module' => $exerciseSource[$type],
                'wrong_number' => $typeCount,
            ];
        }

        return $bankExerciseModule;
    }

    protected function bankExerciseSourceConstant()
    {
        return [
            'chapter' => '章节练习',
            'testpaper' => '考试练习',
        ];
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }
}
