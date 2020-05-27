<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\HomeworkActivityDao;
use Biz\Activity\Service\HomeworkActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class Homework extends Activity
{
    public function sync($activity, $config = [])
    {
        if ('homework' !== $activity['mediaType']) {
            return null;
        }
        $scene = $this->createScene($activity);

        $newExt = [
            'answerSceneId' => $scene['id'],
            'assessmentId' => $activity['assessment']['id'],
        ];

        return $this->create($newExt);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        if ('homework' !== $activity['mediaType']) {
            return null;
        }
        $homeworkActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($homeworkActivity)) {
            return [];
        }
        $existHomework = $this->getHomeworkActivityDao()->search(['syncId' => $homeworkActivity['id']], [], 0, PHP_INT_MAX);

        /**
         * $scene 无法找到对应关系，每次重新创建
         */
        $scene = $this->createScene($activity);
        $newExt = [
            'answerSceneId' => $scene['id'],
            'assessmentId' => $activity['assessment']['id'],
        ];
        if (!empty($existHomework)) {
            return $this->getHomeworkActivityDao()->update($existHomework[0]['id'], $newExt);
        }

        return $this->create($newExt);
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

    public function create($fields)
    {
        return $this->getHomeworkActivityService()->create($fields);
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    /**
     * @return HomeworkActivityDao
     */
    protected function getHomeworkActivityDao()
    {
        return $this->getBiz()->dao('Activity:HomeworkActivityDao');
    }
}
