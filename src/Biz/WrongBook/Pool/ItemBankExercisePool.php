<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
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

        return $this->prepareCommonSceneIds($conditions, $pool['target_id']);
    }

    public function prepareSceneIdsByTargetId($targetId, $conditions)
    {
        return $this->prepareCommonSceneIds($conditions, $targetId);
    }

    public function buildConditions($pool, $conditions)
    {
        $searchConditions = [];

        if (!in_array($conditions['exerciseMediaType'], ['chapter', 'testpaper'])) {
            return [];
        }
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestionsWithCollect(['pool_id' => $pool['id']], [], 0, PHP_INT_MAX);

        $searchConditions['chapter'] = $this->exerciseChapterSearch($pool['target_id'], $conditions);
        $searchConditions['testpaper'] = $this->exerciseAssessmentSearch($pool['target_id'], $conditions, $wrongQuestions);

        return $searchConditions;
    }

    public function buildTargetConditions($targetId, $conditions)
    {
        $searchConditions = [];

        if (!in_array($conditions['exerciseMediaType'], ['chapter', 'testpaper'])) {
            return [];
        }
        $pools = $this->getWrongQuestionService()->searchWrongBookPool(['target_type' => 'exercise', 'target_id' => $targetId], [], 0, PHP_INT_MAX);
        $poolIds = empty($pools) ? [-1] : ArrayToolkit::column($pools, 'id');
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestionsWithCollect(['pool_ids' => $poolIds], [], 0, PHP_INT_MAX);

        $searchConditions['chapter'] = $this->exerciseChapterSearch($targetId, $conditions);
        $searchConditions['testpaper'] = $this->exerciseAssessmentSearch($targetId, $conditions, $wrongQuestions);

        return $searchConditions;
    }

    public function exerciseChapterSearch($targetId, $conditions)
    {
        if ('chapter' !== $conditions['exerciseMediaType']) {
            return [];
        }

        return $this->getItemCategoryService()->getItemCategoryTree($targetId);
    }

    public function exerciseAssessmentSearch($targetId, $conditions, $wrongQuestions)
    {
        if ('testpaper' !== $conditions['exerciseMediaType']) {
            return [];
        }
        $exercise = $this->getItemBankExerciseService()->getByQuestionBankId($targetId);
        $exerciseModules = $this->getExerciseModuleService()->findByExerciseIdAndType($exercise['id'], 'assessment');

        $moduleIds = ArrayToolkit::column($exerciseModules, 'id');
        $assessmentExercises = $this->getItemBankAssessmentExerciseService()->findByModuleIds($moduleIds);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($assessmentExercises, 'assessmentId'));
        $wrongQuestionGroupAssessmentId = ArrayToolkit::group($wrongQuestions, 'testpaper_id');

        $assessmentSearch = [];
        $tempAssessment = [];
        foreach ($assessmentExercises as $exercises) {
            $assessmentId = $exercises['assessmentId'];
            if (isset($wrongQuestionGroupAssessmentId[$exercises['assessmentId']]) && !in_array($assessmentId, $tempAssessment)) {
                $assessmentSearch[] = [
                    'assessmentId' => $assessmentId,
                    'module' => $exercises['moduleId'],
                    'assessmentName' => $assessments[$exercises['assessmentId']]['name'],
                ];
                $tempAssessment[] = $assessmentId;
            }
        }

        return $assessmentSearch;
    }

    public function findSceneIdsByExerciseMediaType($targetId, $mediaType)
    {
        if (!in_array($mediaType, ['chapter', 'assessment'])) {
            return [];
        }

        $exercise = $this->getItemBankExerciseService()->getByQuestionBankId($targetId);
        $exerciseModules = $this->getExerciseModuleService()->findByExerciseIdAndType($exercise['id'], $mediaType);

        return ArrayToolkit::column($exerciseModules, 'answerSceneId');
    }

    protected function prepareCommonSceneIds($conditions, $targetId)
    {
        if (empty($conditions['exerciseMediaType'])) {
            $chapterSceneIds = $this->findSceneIdsByExerciseMediaType($targetId, 'chapter');
            $assessmentSceneIds = $this->findSceneIdsByExerciseMediaType($targetId, 'assessment');
            $sceneIds = array_merge($chapterSceneIds, $assessmentSceneIds);
        } else {
            $mediaType = 'testpaper' === $conditions['exerciseMediaType'] ? 'assessment' : 'chapter';
            $sceneIds = $this->findSceneIdsByExerciseMediaType($targetId, $mediaType);
        }

        return empty($sceneIds) ? [-1] : $sceneIds;
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

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }
}
