<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\ExerciseActivityDao;
use Biz\Activity\Service\ExerciseActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class Exercise extends Activity
{
    public function sync($activity, $config = [])
    {
        if ('exercise' !== $activity['mediaType']) {
            return null;
        }
        $scene = $this->createScene($activity);

        $newExt = [
            'answerSceneId' => $scene['id'],
            'drawCondition' => $this->convertDrawCondition($activity),
        ];

        return $this->create($newExt);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        if ('exercise' !== $activity['mediaType']) {
            return null;
        }
        $exerciseActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($exerciseActivity)) {
            return [];
        }
        $existExercise = $this->getExerciseActivityDao()->search(['syncId' => $exerciseActivity['id']], [], 0, PHP_INT_MAX);

        /**
         * scene无对应关系，每次重新创建
         */
        $scene = $this->createScene($activity);
        $newExt = [
            'answerSceneId' => $scene['id'],
            'drawCondition' => $this->convertDrawCondition($activity),
        ];
        if (!empty($existExercise)) {
            return $this->getExerciseActivityDao()->update($existExercise[0]['id'], $newExt);
        }

        return $this->create($newExt);
    }

    protected function convertDrawCondition($activity)
    {
        $metas = $activity['testpaper']['metas'];
        $drawCondition = [];
        if (isset($metas['range']['lessonId']) || isset($metas['range']['courseId']) || empty($metas['range']) || !is_array($metas['range'])) {
            return $drawCondition;
        }

        $drawCondition['range'] = [
            'question_bank_id' => $metas['range']['bankId'],
            'bank_id' => $metas['range']['bankId'],
            'category_ids' => explode(',', $metas['range']['categoryIds']),
            'difficulty' => empty($metas['difficulty']) ? '' : $metas['difficulty'],
        ];

        $drawCondition['section'] = [
            'conditions' => [
                'item_types' => $metas['questionTypes'],
            ],
            'item_count' => $activity['testpaper']['itemCount'],
            'name' => '练习题目',
        ];

        return $drawCondition;
    }

    public function create($fields)
    {
        return $this->getExerciseActivityService()->createActivity($fields);
    }

    protected function createScene($activity)
    {
        return $this->getAnswerSceneService()->create([
            'name' => $activity['title'],
            'limited_time' => 0,
            'do_times' => 0,
            'redo_interval' => 0,
            'need_score' => 0,
            'manual_marking' => 0,
            'start_time' => 0,
            'pass_score' => 0,
            'enable_facein' => 0,
        ]);
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->getBiz()->service('Activity:ExerciseActivityService');
    }

    /**
     * @return ExerciseActivityDao
     */
    protected function getExerciseActivityDao()
    {
        return $this->getBiz()->dao('Activity:ExerciseActivityDao');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }
}
