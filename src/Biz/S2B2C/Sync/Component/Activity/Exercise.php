<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Service\ExerciseActivityService;

class Exercise extends Activity
{
    public function sync($activity, $config = [])
    {
        if ('exercise' !== $activity['mediaType']) {
            return null;
        }

        $newExt = [
            'answerSceneId' => $activity['answerSceneId'],
            'drawCondition' => $this->convertDrawCondition($activity),
        ];

        return $this->create($newExt);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        if ('exercise' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($testpaperActivity)) {
            return [];
        }
        $newExt = $this->getTestpaperActivityFields($testpaperActivity, $config);
        $newTestpaperFields = $this->filterFields($newExt);

        $existTestpaper = $this->getTestpaperActivityDao()->search(['syncId' => $newTestpaperFields['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existTestpaper)) {
            return $this->getTestpaperActivityDao()->update($existTestpaper[0]['id'], $newTestpaperFields);
        }

        return $this->getTestpaperActivityDao()->create($newTestpaperFields);
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
            'item_count' => $activity['itemCount'],
            'name' => '练习题目',
        ];

        return $drawCondition;
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getExerciseActivityService()->createActivity($fields);
    }

    protected function createScene($activity)
    {
        [
            'id' => $activity['id'],
            'name' => $activity['title'],
            'limited_time' => 0,
            'do_times' => 0,
            'redo_interval' => 0,
            'need_score' => 0,
            'manual_marking' => 0,
            'start_time' => 0,
            'pass_score' => 0,
            'enable_facein' => 0,
        ];
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->getBiz()->service('Activity:ExerciseActivityService');
    }
}
