<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class Homework extends Activity
{
    protected function registerListeners()
    {
        return [];
    }

    public function get($targetId)
    {
        $homework = $this->getHomeworkActivityService()->get($targetId);
        if ($homework) {
            $homework['assessment'] = $this->getAssessmentService()->getAssessment($homework['assessmentId']);
        }

        return $homework;
    }

    public function find($targetIds, $showCloud = 1)
    {
        return $this->getHomeworkActivityService()->findByIds($targetIds);
    }

    public function create($fields)
    {
        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->create([
                'name' => $fields['title'],
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => 0,
                'manual_marking' => 1,
                'start_time' => 0,
            ]);

            $assessment = $this->createAssessment($fields['title'], $fields['description'], $fields['questionIds']);

            $activity = $this->getHomeworkActivityService()->create([
                'answerSceneId' => $answerScene['id'],
                'assessmentId' => $assessment['id'],
            ]);

            $this->getBiz()['db']->commit();

            return $activity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    protected function createAssessment($name, $description, $itemIds = [])
    {
        $items = $this->getItemService()->findItemsByIds($itemIds, true);
        $bankIds = array_column($items, 'bank_id');

        $assessment = [
            'bank_id' => array_shift($bankIds),
            'name' => $name,
            'description' => $description,
            'displayable' => 0,
            'sections' => [
                [
                    'name' => '作业题目',
                    'items' => $items,
                ],
            ],
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        return $this->getAssessmentService()->openAssessment($assessment['id']);
    }

    public function copy($activity, $config = [])
    {
        $homework = $this->get($activity['mediaId']);

        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->get($homework['answerSceneId']);
            $answerScene = $this->getAnswerSceneService()->create([
                'name' => $answerScene['name'],
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => 0,
                'manual_marking' => 1,
                'start_time' => 0,
            ]);

            $activity = $this->getHomeworkActivityService()->create([
                'answerSceneId' => $answerScene['id'],
                'assessmentId' => $homework['assessmentId'],
            ]);

            $this->getBiz()['db']->commit();

            return $activity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceHomework = $this->get($sourceActivity['mediaId']);
        $homework = $this->get($activity['mediaId']);

        $fields = [
            'name' => $sourceHomework['assessment']['name'],
            'description' => $sourceHomework['assessment']['description'],
        ];

        $this->getAssessmentService()->updateBasicAssessment($homework['assessmentId'], $fields);

        return $activity;
    }

    public function update($targetId, &$fields, $activity)
    {
        $homework = $this->get($targetId);

        if (!$homework) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $filterFields = [
            'name' => $fields['title'],
            'description' => $fields['description'],
        ];

        $this->getAssessmentService()->updateBasicAssessment($homework['assessmentId'], $filterFields);

        return $homework;
    }

    public function delete($targetId)
    {
        return $this->getHomeworkActivityService()->delete($targetId);
    }

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId, true);

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId(
            $activity['ext']['answerSceneId'],
            $user['id']
        );

        if (empty($answerRecord)) {
            return false;
        }

        if ('submit' === $activity['finishType'] && in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_REVIEWING, AnswerService::ANSWER_RECORD_STATUS_FINISHED])) {
            return true;
        }

        return false;
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }
}
