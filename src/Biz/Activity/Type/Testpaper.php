<?php

namespace Biz\Activity\Type;

use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Activity\Service\TestpaperActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class Testpaper extends Activity
{
    protected function registerListeners()
    {
        return array(
            'activity.created' => 'Biz\Activity\Listener\TestpaperActivityCreateListener',
        );
    }

    public function get($targetId)
    {
        $activity = $this->getTestpaperActivityService()->getActivity($targetId);
        if ($activity) {
            $testpaper = $this->getAssessmentService()->getAssessment($activity['mediaId']);
            $activity['testpaper'] = $testpaper;
            $activity['answerScene'] = $this->getAnswerSceneService()->get($activity['answerSceneId']);
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

            $answerScene = $this->getAnswerSceneService()->create(array(
                'name' => $fields['title'],
                'limited_time' => $fields['limitedTime'],
                'do_times' => $fields['doTimes'],
                'redo_interval' => $fields['redoInterval'],
                'need_score' => 1,
                'start_time' => $fields['startTime'],
                'pass_score' => empty($fields['passScore']) ? 0 : $fields['passScore'],
            ));

            $testpaperActivity = $this->getTestpaperActivityService()->createActivity(array(
                'mediaId' => $fields['mediaId'],
                'checkType' => empty($fields['checkType']) ? '' : $fields['checkType'],
                'requireCredit' => empty($fields['requireCredit']) ? 0 : $fields['requireCredit'],
                'answerSceneId' => $answerScene['id'],
                'finishCondition' => $fields['finishCondition'],
            ));

            $this->getBiz()['db']->commit();

            return $testpaperActivity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    public function copy($activity, $config = array())
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $this->get($activity['mediaId']);

        $newExt = array(
            'title' => $activity['title'],
            'startTime' => $activity['startTime'],
            'testpaperId' => $testpaperActivity['mediaId'],
            'doTimes' => $testpaperActivity['answerScene']['do_times'],
            'redoInterval' => $testpaperActivity['answerScene']['redo_interval'],
            'limitedTime' => $testpaperActivity['answerScene']['limited_time'],
            'checkType' => $testpaperActivity['checkType'],
            'requireCredit' => $testpaperActivity['requireCredit'],
            'testMode' => $testpaperActivity['testMode'],
            'finishCondition' => $testpaperActivity['finishCondition'],
        );

        return $this->create($newExt);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExt = $this->get($sourceActivity['mediaId']);
        $ext = $this->get($activity['mediaId']);

        $ext['startTime'] = $sourceActivity['startTime'];
        $ext['title'] = $sourceActivity['title'];
        $ext['testpaperId'] = $sourceExt['mediaId'];
        $ext['doTimes'] = $sourceExt['answerScene']['do_times'];
        $ext['redoInterval'] = $sourceExt['answerScene']['redo_interval'];
        $ext['limitedTime'] = $sourceExt['answerScene']['limited_time'];
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
        if (0 == $fields['doTimes'] || 'normal' == $fields['testMode']) {
            $fields['startTime'] = 0;
        }

        $filterFields = $this->filterFields($fields);

        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->update($activity['answerScene']['id'], array(
                'name' => $filterFields['title'],
                'limited_time' => $filterFields['limitedTime'],
                'do_times' => $filterFields['doTimes'],
                'redo_interval' => $filterFields['redoInterval'],
                'start_time' => $filterFields['startTime'],
                'pass_score' => empty($filterFields['passScore']) ? 0 : $filterFields['passScore'],
            ));

            $testpaperActivity = $this->getTestpaperActivityService()->updateActivity($activity['id'], array(
                'mediaId' => $filterFields['mediaId'],
                'checkType' => empty($filterFields['checkType']) ? '' : $filterFields['checkType'],
                'requireCredit' => empty($filterFields['requireCredit']) ? 0 : $filterFields['requireCredit'],
                'finishCondition' => $filterFields['finishCondition'],
            ));

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
            array(AnswerService::ANSWER_RECORD_STATUS_REVIEWING, AnswerService::ANSWER_RECORD_STATUS_FINISHED)
        )) {
            return false;
        }

        if ('submit' === $activity['finishType']) {
            return true;
        }

        $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
        if ($answerRecord['status'] == AnswerService::ANSWER_RECORD_STATUS_FINISHED && 'score' === $activity['finishType'] && $answerReport['score'] >= $testpaperActivity['finishCondition']['finishScore']) {
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
                $fields['finishCondition'] = array(
                    'type' => 'score',
                    'finishScore' => $passScore,
                );
            } else {
                $fields['finishCondition'] = array();
            }
        }

        $filterFields = ArrayToolkit::parts(
            $fields,
            array(
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
            )
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
