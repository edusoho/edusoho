<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Activity\Type\Testpaper;
use Biz\Question\Traits\QuestionAIAnalysisTrait;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;

class AnswerRecord extends AbstractResource
{
    use QuestionAIAnalysisTrait;

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

        /**
         * 如果在考试中 已关闭的试卷 仍然能进行考试不会强行结束
         * if ('open' !== $assessment['status']) {
            }
         */
        $assessment = $this->getAnswerRandomSeqService()->shuffleItemsAndOptionsIfNecessary($assessment, $answerRecord['id']);

        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);
        $assessmentResponse = $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        // 之前业务中只有考试需要做特殊处理，应该用到customComments字段
        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerScene['id']);

        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerScene['id']);

        $user = $this->getCurrentUser();
        $activity['isOnlyStudent'] = $user['roles'] == ['ROLE_USER'];
        $resultShow = empty($testpaperActivity) || $this->getResultShow($answerRecord, $answerScene, $answerReport);
        if ($resultShow) {
            $assessment = $this->wrapAIAnalysis($assessment);
        }

        return [
            'answer_report' => $answerReport,
            'answer_record' => $this->wrapperAnswerRecord($answerRecord),
            'assessment_response' => $assessmentResponse,
            'assessment' => $assessment,
            'answer_scene' => $this->wrapperAnswerScene($answerScene),
            'resultShow' => $resultShow,
            'activity' => empty($testpaperActivity) ? (object) [] : $testpaperActivity,
            'metaActivity' => empty($activity) ? (object) [] : $activity,
        ];
    }

    private function wrapAIAnalysis($assessment)
    {
        foreach ($assessment['sections'] as &$section) {
            foreach ($section['items'] as &$item) {
                foreach ($item['questions'] as &$question) {
                    $question['aiAnalysisEnable'] = $this->canGenerateAIAnalysis($question, $item);
                }
            }
        }

        return $assessment;
    }

    protected function wrapperAnswerScene($answerScene)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerScene['id']);
        $answerScene['fromType'] = $activity['mediaType'];
        $answerScene['reviewType'] = 'testpaper' == $activity['mediaType'] || 'score' == $activity['finishType'] ? 'score' : 'true_false';
        $answerScene['isLimitDoTimes'] = empty($answerScene['do_times']) ? '0' : '1';
        $countTestpaperRecord = $this->getAnswerRecordService()->count(['answer_scene_id' => $answerScene['id'], 'user_id' => $this->getCurrentUser()->getId()]);
        $answerScene['remainderDoTimes'] = max($answerScene['do_times'] - ($countTestpaperRecord ?: 0), 0);
        $answerScene['validPeriodMode'] = $this->preValidPeriodMode($answerScene);
        $answerScene['canDoAgain'] = $this->getAnswerSceneService()->canStart($answerScene['id'], $this->getCurrentUser()->getId()) ? '1' : '0';

        return $answerScene;
    }

    protected function preValidPeriodMode($scene)
    {
        if (!empty($scene['start_time']) && !empty($scene['end_time'])) {
            $validPeriodMode = Testpaper::VALID_PERIOD_MODE_RANGE;
        } elseif (!empty($scene['start_time']) && empty($scene['end_time'])) {
            $validPeriodMode = Testpaper::VALID_PERIOD_MODE_ONLY_START;
        } else {
            $validPeriodMode = Testpaper::VALID_PERIOD_MODE_NO_LIMIT;
        }

        return $validPeriodMode;
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

    /**
     * @return AnswerRandomSeqService
     */
    protected function getAnswerRandomSeqService()
    {
        return $this->service('ItemBank:Answer:AnswerRandomSeqService');
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
