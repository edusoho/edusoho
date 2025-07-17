<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class Testpaper extends Activity
{
    // 考试及格后显示答案
    const ANSWER_MODE_PASSED = 1;

    const EXAM_MODE_SIMULATION = 0;

    const EXAM_MODE_PRACTICE = 1;

    const VALID_PERIOD_MODE_NO_LIMIT = 0;

    const VALID_PERIOD_MODE_RANGE = 1;

    const VALID_PERIOD_MODE_ONLY_START = 2;

    protected function registerListeners()
    {
        return [
            'activity.created' => 'Biz\Activity\Listener\TestpaperActivityCreateListener',
        ];
    }

    public function get($targetId)
    {
        $activity = $this->getTestpaperActivityService()->getActivity($targetId);
        if ($activity) {
            $testpaper = $this->getAssessmentService()->getAssessment($activity['mediaId']);
            $activity['testpaper'] = $testpaper;
            $activity['answerScene'] = $this->getAnswerSceneService()->get($activity['answerSceneId']);
            $activity = $this->filterActivity($activity, $activity['answerScene']);
        }

        return $activity;
    }

    public function find($ids, $showCloud = 1)
    {
        return $this->getTestpaperActivityService()->findActivitiesByIds($ids);
    }

    public function create($fields)
    {
        $fields = $this->preFields($fields);
        $this->checkFields($fields);
        $fields = $this->filterFields($fields);

        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->create([
                'name' => $fields['title'],
                'limited_time' => $fields['limitedTime'],
                'do_times' => $fields['doTimes'],
                'redo_interval' => $fields['redoInterval'],
                'need_score' => 1,
                'start_time' => $fields['startTime'],
                'valid_period_mode' => 3 == $fields['validPeriodMode'] ? $fields['validPeriodMode'] : 0,
                'pass_score' => empty($fields['passScore']) ? 0 : $fields['passScore'],
                'enable_facein' => empty($fields['enable_facein']) ? 0 : $fields['enable_facein'],
                'exam_mode' => empty($fields['exam_mode']) ? self::EXAM_MODE_SIMULATION : $fields['exam_mode'],
                'end_time' => empty($fields['endTime']) ? 0 : $fields['endTime'],
                'is_items_seq_random' => empty($fields['isItemsSeqRandom']) ? 0 : $fields['isItemsSeqRandom'],
                'is_options_seq_random' => empty($fields['isOptionsSeqRandom']) ? 0 : $fields['isOptionsSeqRandom'],
            ]);

            $testpaperActivity = $this->getTestpaperActivityService()->createActivity([
                'mediaId' => $fields['mediaId'],
                'checkType' => empty($fields['checkType']) ? '' : $fields['checkType'],
                'requireCredit' => empty($fields['requireCredit']) ? 0 : $fields['requireCredit'],
                'answerSceneId' => $answerScene['id'],
                'finishCondition' => $fields['finishCondition'],
                'answerMode' => $fields['answerMode'],
                'customComments' => $fields['customComments'],
            ]);

            $this->getBiz()['db']->commit();
            if (!empty($answerScene['end_time'])) {
                $this->registerJob($answerScene);
            }

            return $testpaperActivity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    public function copy($activity, $config = [])
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $this->get($activity['mediaId']);

        $newExt = [
            'title' => $activity['title'],
            'startTime' => $activity['startTime'],
            'finishData' => $activity['finishData'],
            'testpaperId' => $testpaperActivity['mediaId'],
            'doTimes' => $testpaperActivity['answerScene']['do_times'],
            'redoInterval' => $testpaperActivity['answerScene']['redo_interval'],
            'limitedTime' => $testpaperActivity['answerScene']['limited_time'],
            'enable_facein' => $testpaperActivity['answerScene']['enable_facein'],
            'exam_mode' => $testpaperActivity['answerScene']['exam_mode'],
            'checkType' => $testpaperActivity['checkType'],
            'requireCredit' => $testpaperActivity['requireCredit'],
            'testMode' => $testpaperActivity['testMode'],
            'finishCondition' => $testpaperActivity['finishCondition'],
            'answerMode' => $testpaperActivity['answerMode'],
            'endTime' => $testpaperActivity['answerScene']['end_time'],
            'isItemsSeqRandom' => $testpaperActivity['answerScene']['is_items_seq_random'],
            'isOptionsSeqRandom' => $testpaperActivity['answerScene']['is_options_seq_random'],
            'isCopy' => 1,
            'isLimitDoTimes' => $testpaperActivity['isLimitDoTimes'],
            'customComments' => $testpaperActivity['customComments'],
        ];
        $newExt['validPeriodMode'] = $this->preValidPeriodMode($testpaperActivity['answerScene']);

        return $this->create($newExt);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExt = $this->get($sourceActivity['mediaId']);
        $ext = $this->get($activity['mediaId']);

        $ext['startTime'] = $sourceActivity['startTime'];
        $ext['title'] = $sourceActivity['title'];
        $ext['finishData'] = $sourceActivity['finishData'];
        $ext['testpaperId'] = $sourceExt['mediaId'];
        $ext['doTimes'] = $sourceExt['answerScene']['do_times'];
        $ext['redoInterval'] = $sourceExt['answerScene']['redo_interval'];
        $ext['limitedTime'] = $sourceExt['answerScene']['limited_time'];
        $ext['enable_facein'] = $sourceExt['answerScene']['enable_facein'];
        $ext['exam_mode'] = $sourceExt['answerScene']['exam_mode'];
        $ext['checkType'] = $sourceExt['checkType'];
        $ext['requireCredit'] = $sourceExt['requireCredit'];
        $ext['testMode'] = $sourceExt['testMode'];
        $ext['finishCondition'] = $sourceExt['finishCondition'];
        $ext['answerMode'] = $sourceExt['answerMode'];
        $ext['endTime'] = $sourceExt['answerScene']['end_time'];
        $ext['isItemsSeqRandom'] = $sourceExt['answerScene']['is_items_seq_random'];
        $ext['isOptionsSeqRandom'] = $sourceExt['answerScene']['is_options_seq_random'];
        $ext['isSync'] = 1;
        $ext['isLimitDoTimes'] = $sourceExt['isLimitDoTimes'];
        $ext['customComments'] = $sourceExt['customComments'];

        return $this->update($ext['id'], $ext, $activity);
    }

    public function update($targetId, &$fields, $activity)
    {
        $activity = $this->get($targetId);

        if (!$activity) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        $fields = $this->preFields($fields);
        $this->checkFields($fields, $activity);
        $filterFields = $this->filterFields($fields);

        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->update($activity['answerScene']['id'], [
                'name' => $filterFields['title'],
                'limited_time' => $filterFields['limitedTime'],
                'do_times' => $filterFields['doTimes'],
                'redo_interval' => $filterFields['redoInterval'],
                'start_time' => $filterFields['startTime'],
                'pass_score' => empty($filterFields['passScore']) ? 0 : $filterFields['passScore'],
                'enable_facein' => empty($filterFields['enable_facein']) ? 0 : $filterFields['enable_facein'],
                'exam_mode' => empty($filterFields['exam_mode']) ? self::EXAM_MODE_SIMULATION : $filterFields['exam_mode'],
                'end_time' => empty($filterFields['endTime']) ? 0 : $filterFields['endTime'],
                'is_items_seq_random' => empty($filterFields['isItemsSeqRandom']) ? 0 : $filterFields['isItemsSeqRandom'],
                'is_options_seq_random' => empty($filterFields['isOptionsSeqRandom']) ? 0 : $filterFields['isOptionsSeqRandom'],
            ]);

            $testpaperActivity = $this->getTestpaperActivityService()->updateActivity($activity['id'], [
                'mediaId' => $filterFields['mediaId'],
                'checkType' => empty($filterFields['checkType']) ? '' : $filterFields['checkType'],
                'requireCredit' => empty($filterFields['requireCredit']) ? 0 : $filterFields['requireCredit'],
                'finishCondition' => $filterFields['finishCondition'],
                'answerMode' => $filterFields['answerMode'],
                'customComments' => $filterFields['customComments'],
            ]);

            $this->getBiz()['db']->commit();
            if (!empty($filterFields['endTime']) && $filterFields['endTime'] != $activity['answerScene']['end_time']) {
                $this->registerJob($answerScene);
            }

            return $testpaperActivity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    public function delete($targetId)
    {
        return $this->getTestpaperActivityService()->deleteActivity($targetId);
    }

    public function isFinished($activityId, $userId = 0)
    {
        $userId = empty($userId) ? $this->getCurrentUser()->getId() : $userId;

        $activity = $this->getActivityService()->getActivity($activityId, true);
        $testpaperActivity = $activity['ext'];

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId(
            $testpaperActivity['answerScene']['id'],
            $userId
        );

        if (empty($answerRecord)) {
            return false;
        }
        if (!in_array(
            $answerRecord['status'],
            [AnswerService::ANSWER_RECORD_STATUS_REVIEWING, AnswerService::ANSWER_RECORD_STATUS_FINISHED]
        )) {
            return false;
        }

        if ('submit' === $activity['finishType']) {
            return true;
        }

        $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
        if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status'] && 'score' === $activity['finishType'] && $answerReport['score'] >= $testpaperActivity['finishCondition']['finishScore']) {
            return true;
        }

        return false;
    }

    protected function preFields($fields)
    {
        if (isset($fields['validPeriodMode'])) {
            if (self::VALID_PERIOD_MODE_ONLY_START == $fields['validPeriodMode']) {
                $fields['endTime'] = 0;
            } elseif (self::VALID_PERIOD_MODE_NO_LIMIT == $fields['validPeriodMode']) {
                $fields['startTime'] = 0;
                $fields['endTime'] = 0;
            }
        }

        return $fields;
    }

    protected function checkFields($fields, $activity = null)
    {
        if (isset($fields['isCopy']) || isset($fields['isSync'])) {
            return;
        }

        if (!empty($fields['isLimitDoTimes']) && !empty($fields['doTimes']) && $fields['doTimes'] > 100) {
            throw TestpaperException::TESTPAPER_DOTIMES_LIMIT();
        }

        if (!empty($fields['endTime']) && $fields['endTime'] <= $fields['startTime']) {
            throw TestpaperException::END_TIME_EARLIER();
        }

        if (!empty($activity) && !empty($fields['startTime']) && $fields['startTime'] == $activity['answerScene']['start_time']) {
            return;
        }
        if (!empty($fields['startTime']) && $fields['startTime'] < time()) {
            throw TestpaperException::START_TIME_EARLIER_THAN_CURRENT_TIME();
        }
    }

    protected function filterFields($fields)
    {
        $testPaper = $this->getAssessmentService()->getAssessment($fields['testpaperId']);
        $fields['passScore'] = empty($fields['finishData']) ? 0 : round($testPaper['total_score'] * $fields['finishData']);

        if (!empty($fields['finishType'])) {
            $fields['finishCondition'] = [];
            if ('score' == $fields['finishType']) {
                $fields['finishCondition'] = [
                    'type' => 'score',
                    'finishScore' => $fields['passScore'],
                ];
            }
        }

        if (!isset($fields['customComments'])) {
            $fields['customComments'] = [];
            if (!empty($fields['start'])) {
                foreach ($fields['start'] as $key => $val) {
                    $fields['customComments'][] = [
                        'start' => $val,
                        'end' => $fields['end'][$key],
                        'comment' => $fields['comment'][$key],
                    ];
                }
            }
        }

        $fields['doTimes'] = empty($fields['isLimitDoTimes']) ? '0' : $fields['doTimes'];

        $filterFields = ArrayToolkit::parts(
            $fields,
            [
                'title',
                'testpaperId',
                'doTimes',
                'redoInterval',
                'length',
                'limitedTime',
                'checkType',
                'requireCredit',
                'testMode',
                'finishCondition',
                'startTime',
                'passScore',
                'enable_facein',
                'answerMode',
                'customComments',
                'exam_mode',
                'endTime',
                'isItemsSeqRandom',
                'isOptionsSeqRandom',
                'validPeriodMode',
            ]
        );

        if (isset($filterFields['length'])) {
            $filterFields['limitedTime'] = $filterFields['length'];
            unset($filterFields['length']);
        }

        $filterFields['answerMode'] = isset($fields['answerMode']) ? $fields['answerMode'] : 0;

        $filterFields['mediaId'] = $filterFields['testpaperId'];
        unset($filterFields['testpaperId']);

        return $filterFields;
    }

    protected function filterActivity($activity, $scene)
    {
        $userId = $this->getCurrentUser()->getId();
        if (!empty($scene)) {
            $activity['doTimes'] = $scene['do_times'];
            $activity['redoInterval'] = $scene['redo_interval'];
            $activity['limitedTime'] = $scene['limited_time'];
            $activity['testMode'] = !empty($scene['start_time']) ? 'realTime' : 'normal';
            $activity['isLimitDoTimes'] = empty($scene['do_times']) ? '0' : '1';
            $activity['validPeriodMode'] = $this->preValidPeriodMode($scene);
            $countTestpaperRecord = $this->getAnswerRecordService()->count(['answer_scene_id' => $scene['id'], 'user_id' => $userId]);
            $activity['remainderDoTimes'] = max($scene['do_times'] - ($countTestpaperRecord ?: 0), 0);
            $activity['canDoAgain'] = $this->getAnswerSceneService()->canStart($scene['id'], $userId) ? '1' : '0';
        }

        return $activity;
    }

    protected function preValidPeriodMode($scene)
    {
        if (!empty($scene['start_time']) && !empty($scene['end_time'])) {
            $validPeriodMode = self::VALID_PERIOD_MODE_RANGE;
        } elseif (!empty($scene['start_time']) && empty($scene['end_time'])) {
            $validPeriodMode = self::VALID_PERIOD_MODE_ONLY_START;
        } else {
            $validPeriodMode = self::VALID_PERIOD_MODE_NO_LIMIT;
        }
        if (isset($scene['valid_period_mode']) && 3 == $scene['valid_period_mode']) {
            $validPeriodMode = $scene['valid_period_mode'];
        }

        return $validPeriodMode;
    }

    private function registerJob($scene)
    {
        $this->getSchedulerService()->deleteJobByName('noAnswerAssessmentAutoSubmitJob_'.$scene['id']);

        $executeTime = strtotime(date('Y-m-d H:i', $scene['end_time']));
        $executeTime = $scene['end_time'] > $executeTime ? $executeTime + 60 : $executeTime;
        $this->getSchedulerService()->register([
            'name' => 'noAnswerAssessmentAutoSubmitJob_'.$scene['id'],
            'expression' => intval($executeTime),
            'class' => 'Biz\Testpaper\Job\NoAnswerAssessmentAutoSubmitJob',
            'misfire_threshold' => 60 * 10,
            'misfire_policy' => 'executing',
            'args' => ['answerSceneId' => $scene['id']],
        ]);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
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
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }

    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }
}
