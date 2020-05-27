<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class Testpaper extends Activity
{
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
                'pass_score' => empty($fields['passScore']) ? 0 : $fields['passScore'],
                'enable_facein' => empty($fields['enable_facein']) ? 0 : $fields['enable_facein'],
            ]);

            $testpaperActivity = $this->getTestpaperActivityService()->createActivity([
                'mediaId' => $fields['mediaId'],
                'checkType' => empty($fields['checkType']) ? '' : $fields['checkType'],
                'requireCredit' => empty($fields['requireCredit']) ? 0 : $fields['requireCredit'],
                'answerSceneId' => $answerScene['id'],
                'finishCondition' => $fields['finishCondition'],
            ]);

            $this->getBiz()['db']->commit();

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
            'checkType' => $testpaperActivity['checkType'],
            'requireCredit' => $testpaperActivity['requireCredit'],
            'testMode' => $testpaperActivity['testMode'],
            'finishCondition' => $testpaperActivity['finishCondition'],
        ];

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
        $ext['checkType'] = $sourceExt['checkType'];
        $ext['requireCredit'] = $sourceExt['requireCredit'];
        $ext['testMode'] = $sourceExt['testMode'];
        $ext['finishCondition'] = $sourceExt['finishCondition'];

        return $this->update($ext['id'], $ext, $activity);
    }

    public function update($targetId, &$fields, $activity)
    {
        $activity = $this->get($targetId);

        if (!$activity) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        //引用传递，当考试时间设置改变时，时间值也改变
        if (0 == $fields['doTimes']) {
            $fields['startTime'] = 0;
        }

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
            ]);

            $testpaperActivity = $this->getTestpaperActivityService()->updateActivity($activity['id'], [
                'mediaId' => $filterFields['mediaId'],
                'checkType' => empty($filterFields['checkType']) ? '' : $filterFields['checkType'],
                'requireCredit' => empty($filterFields['requireCredit']) ? 0 : $filterFields['requireCredit'],
                'finishCondition' => $filterFields['finishCondition'],
            ]);

            $this->getBiz()['db']->commit();

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

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId, true);
        $testpaperActivity = $activity['ext'];

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId(
            $testpaperActivity['answerScene']['id'],
            $user['id']
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

    protected function filterFields($fields)
    {
        $testPaper = $this->getAssessmentService()->getAssessment($fields['testpaperId']);
        $fields['passScore'] = empty($fields['finishData']) ? 0 : round($testPaper['total_score'] * $fields['finishData'], 0);

        if (!empty($fields['finishType'])) {
            if ('score' == $fields['finishType']) {
                $fields['finishCondition'] = [
                    'type' => 'score',
                    'finishScore' => $fields['passScore'],
                ];
            } else {
                $fields['finishCondition'] = [];
            }
        }

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
            ]
        );

        if (isset($filterFields['length'])) {
            $filterFields['limitedTime'] = $filterFields['length'];
            unset($filterFields['length']);
        }

        if (isset($filterFields['doTimes']) && 0 == $filterFields['doTimes']) {
            $filterFields['testMode'] = 'normal';
        }

        $filterFields['mediaId'] = $filterFields['testpaperId'];
        unset($filterFields['testpaperId']);

        return $filterFields;
    }

    protected function filterActivity($activity, $scene)
    {
        if (!empty($scene)) {
            $activity['doTimes'] = $scene['do_times'];
            $activity['redoInterval'] = $scene['redo_interval'];
            $activity['limitedTime'] = $scene['limited_time'];
            $activity['testMode'] = !empty($scene['start_time']) ? 'realTime' : 'normal';
        }

        return $activity;
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
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
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
}
