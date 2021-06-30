<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;

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

        $sceneIds = [];
        if (!empty($conditions['exerciseMediaType'])) {
            $sceneIds['sceneIds'] = $this->findSceneIdsByExerciseMediaType($pool['target_id'], $conditions['exerciseMediaType']);
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

    public function buildConditions($pool, $conditions)
    {
        $searchConditions = [];

        if (!in_array($conditions['exerciseMediaType'], ['chapter', 'testpaper'])) {
            return [];
        }

        $searchConditions['chapter'] = $this->exerciseChapterSearch($pool['target_id'], $conditions);
        $searchConditions['testpaper'] = $this->exerciseAssessmentSearch($pool['target_id'], $conditions);

        return $searchConditions;
    }

    public function exerciseChapterSearch($targetId, $conditions)
    {
        if ('chapter' !== $conditions['exerciseMediaType']) {
            return [];
        }

        return $this->getItemCategoryService()->getItemCategoryTree($targetId);
    }

    public function exerciseAssessmentSearch($targetId, $conditions)
    {
        if ('testpaper' !== $conditions['exerciseMediaType']) {
            return [];
        }
        $exerciseModule = $this->getExerciseModuleService()->findByExerciseIdAndType($targetId, 'assessment');
        $moduleId = $exerciseModule[0]['id'];
        $sceneId = $exerciseModule[0]['answerSceneId'];
        $assessmentExercises = $this->getItemBankAssessmentExerciseService()->findByExerciseIdAndModuleId($targetId, $moduleId);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($assessmentExercises, 'assessmentId'));

        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestion([
            'user_id' => $this->getCurrentUser()->getId(),
            'answer_scene_id' => $sceneId,
            'testpaper_ids' => ArrayToolkit::column($assessmentExercises, 'assessmentId'),
        ], [], 0, PHP_INT_MAX);
        $wrongQuestionGroupAssessmentId = ArrayToolkit::group($wrongQuestions, 'testpaper_id');

        $assessmentSearch = [];
        foreach ($assessmentExercises as $exercises) {
            if (isset($wrongQuestionGroupAssessmentId[$exercises['assessmentId']])) {
                $assessmentSearch[] = [
                    'assessmentId' => $exercises['assessmentId'],
                    'answerSceneId' => $sceneId,
                    'assessmentName' => $assessments[$exercises['assessmentId']]['name'],
                ];
            }
        }

        return $assessmentSearch;
    }

    public function findSceneIdsByExerciseMediaType($targetId, $mediaType)
    {
        if (!in_array($mediaType, ['chapter', 'assessment'])) {
            return [];
        }

        $exercise = $this->getExerciseModuleService()->findByExerciseIdAndType($targetId, $mediaType);

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
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return  $this->biz->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->biz->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getItemBankAssessmentExerciseService()
    {
        return $this->biz->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }
}
