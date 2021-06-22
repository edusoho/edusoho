<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class ItemBankExercisePool extends AbstractPool
{
    public function getPoolTarget($report)
    {
        // TODO: Implement getPoolTarget() method.
    }

    public function prepareExerciseSceneIds($poolId, $conditions)
    {
        return parent::prepareSceneIds($this, $poolId, $conditions);
    }

    public function findSceneIdsByExerciseMediaType($poolId, $mediaType)
    {
        if (!in_array($mediaType, ['chapter', 'assessment'])) {
            return [];
        }

        $pool = $this->getWrongQuestionBookPoolDao()->get($poolId);
        if (empty($pool) || 'exercise' != $pool['target_type']) {
            return [];
        }

        $exercise = $this->getExerciseModuleService()->findByExerciseIdAndType($pool['target_id'], $mediaType);

        return ArrayToolkit::column($exercise, 'answerSceneId');
    }

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->biz->dao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }
}
