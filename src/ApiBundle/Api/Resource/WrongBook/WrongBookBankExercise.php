<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
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

        if (empty($pool) || 'exercise' !== $pool['target_type']) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_NOT_EXIST();
        }
        $bankExercise = $this->getItemBankExerciseService()->getByQuestionBankId($pool['target_id']);
        $exerciseModules = $this->getExerciseModuleService()->findByExerciseId($bankExercise['id']);
        $bankExerciseModule = [];
        $bankPool = $this->biz['wrong_question.exercise_pool'];
        $exerciseSource = $this->bankExerciseSourceConstant();

        foreach ($exerciseModules as $module) {
            $condition['exerciseMediaType'] = $type = 'assessment' === $module['type'] ? 'testpaper' : 'chapter';
            $condition['user_id'] = $pool['user_id'];
            $sceneId = $bankPool->prepareSceneIds($poolId, $condition);
            $wrongQuestionByScene = $this->getWrongQuestionService()->findWrongQuestionsByUserIdAndSceneIds($pool['user_id'], $sceneId);
            $typeCount = count(array_unique(ArrayToolkit::column($wrongQuestionByScene, 'collect_id')));
            $bankExerciseModule[] = [
                'type' => $type,
                'module' => $exerciseSource[$type],
                'wrong_number' => $typeCount,
                'cover' => $this->transformImages($bankExercise['cover']),
            ];
        }

        return $bankExerciseModule;
    }

    protected function transformImages(&$images)
    {
        $defaultImg = 'item_bank_exercise.png';
        $images['small'] = AssetHelper::getFurl(empty($images['small']) ? '' : $images['small'], $defaultImg);
        $images['middle'] = AssetHelper::getFurl(empty($images['middle']) ? '' : $images['middle'], $defaultImg);
        $images['large'] = AssetHelper::getFurl(empty($images['large']) ? '' : $images['large'], $defaultImg);

        return $images;
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
