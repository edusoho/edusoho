<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Activity\Type\Testpaper;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;

class AnswerRecord extends AbstractResource
{
    public function get(ApiRequest $request, $id)
    {
        $answerRecord = $this->getAnswerRecordService()->get($id);
        if (empty($answerRecord['answer_report_id'])) {
            return (object) [];
        }

        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $answerReportFilter = new AnswerReportFilter();
        $answerReportFilter->filter($answerReport);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        if ('open' !== $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
        }

        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerScene['id']);


        return [
            'answer_report' => $answerReport,
            'answer_record' => $this->wrapperAnswerRecord($answerRecord),
            'assessment' => $assessment,
            'answer_scene' => $this->wrapperAnswerScene($answerScene),
            'resultShow' => empty($testpaperActivity) ? true : $this->getResultShow($answerRecord, $answerScene, $answerReport),
            'activity' => empty($testpaperActivity) ? (object)[] : $testpaperActivity,
        ];
    }

    protected function wrapperAnswerScene($answerScene)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerScene['id']);
        $answerScene['fromType'] = $activity['mediaType'];
        $answerScene['reviewType'] =  $activity['mediaType'] == 'testpaper' || $activity['finishType'] == 'score'  ? 'score' : 'true_false';
        return $answerScene;
    }

    protected function wrapperAnswerRecord($answerRecord)
    {
        $user = $this->getUserService()->getUser($answerRecord['user_id']);
        $answerRecord['username'] = $user['nickname'];

        return $answerRecord;
    }

    protected function getResultShow($answerRecord, $answerScene, $answerReport)
    {
        $questionSetting = $this->getSettingService()->get('questions', []);

        $answerShowMode = empty($questionSetting['testpaper_answers_show_mode']) ? 'submitted' : $questionSetting['testpaper_answers_show_mode'];

        $resultShow = true;
        if ('hide' == $answerShowMode) {
            $resultShow = false;
        }

        //客观题自动批阅完后先显示答案解析
        if ('reviewed' == $answerShowMode && 'finished' != $answerRecord['status']) {
            $resultShow = false;
        }

        if ('submitted' === $answerShowMode) {
            $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerScene['id']);
            if (0 == $answerScene['do_times'] && Testpaper::ANSWER_MODE_PASSED == $testpaperActivity['answerMode']) {
                if ('finished' === $answerRecord['status'] && $answerReport['score'] >= $answerScene['pass_score']) {
                    $resultShow = true;
                } else {
                    $resultShow = false;
                }
            }
        }

        return $resultShow;
    }

    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
