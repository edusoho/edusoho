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

    public function prepareSceneIds($poolId, $conditions)
    {
        $pool = $this->getWrongQuestionBookPoolDao()->get($poolId);
        if (empty($pool) || 'exercise' != $pool['target_type']) {
            return [];
        }

        return $this->prepareCommonSceneIds($conditions, $pool['target_id']);
    }

    public function prepareSceneIdsByTargetId($targetId, $conditions)
    {
        return $this->prepareCommonSceneIds($conditions, $targetId);
    }

    public function buildConditions($pool, $conditions)
    {
        $searchConditions = [];
        $searchConditions['types'] = $this->exerciseMediaTypeSearch($pool['target_id']);

        return $searchConditions;
    }

    public function exerciseMediaTypeSearch($targetId)
    {
        $exerciseModules = $this->getExerciseModuleService()->findByExerciseId($targetId);
        $mediaType = ArrayToolkit::column($exerciseModules, 'type');

        return array_values((array_unique($mediaType)));
    }

    public function findSceneIdsByExerciseMediaType($targetId, $mediaType)
    {
        if (!in_array($mediaType, ['chapter', 'assessment'])) {
            return [];
        }

        $exercise = $this->getExerciseModuleService()->findByExerciseIdAndType($targetId, $mediaType);

        return ArrayToolkit::column($exercise, 'answerSceneId');
    }

    protected function prepareCommonSceneIds($conditions, $targetId)
    {
        $sceneIds = [];

        if (empty($conditions['exerciseMediaType'])) {
            $chapterSceneIds = $this->findSceneIdsByExerciseMediaType($targetId, 'chapter');
            $assessmentSceneIds = $this->findSceneIdsByExerciseMediaType($targetId, 'assessment');

            return array_merge($chapterSceneIds, $assessmentSceneIds);
        }

        if (!empty($conditions['exerciseMediaType'])) {
            $sceneIds['sceneIds'] = $this->findSceneIdsByExerciseMediaType($targetId, $conditions['exerciseMediaType']);
        }

        if (!isset($sceneIds['sceneIds'])) {
            $sceneIds = [];
        } elseif ($sceneIds['sceneIds'] == []) {
            $sceneIds = [-1];
        } else {
            $sceneIds = $sceneIds['sceneIds'];
        }

        return $sceneIds;
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
