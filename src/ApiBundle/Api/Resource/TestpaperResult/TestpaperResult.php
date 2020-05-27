<?php

namespace ApiBundle\Api\Resource\TestpaperResult;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Question\Service\QuestionService;
use Biz\System\Service\SettingService;
use Biz\Testpaper\TestpaperException;
use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;

class TestpaperResult extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        $data = $request->request->all();
        $testpaperRecord = $this->getAnswerRecordService()->get($data['resultId']);

        if (!empty($testpaperRecord) && !in_array($testpaperRecord['status'], ['doing', 'paused'])) {
            throw TestpaperException::FORBIDDEN_DUPLICATE_COMMIT();
        }

        $wrapper = new AssessmentResponseWrapper();
        $assessment = $this->getAssessmentService()->showAssessment($testpaperRecord['assessment_id']);
        $assessmentResponse = $wrapper->wrap($data, $assessment, $testpaperRecord);
        $testpaperRecord = $this->getAnswerService()->submitAnswer($assessmentResponse);

        $testpaperWrapper = new TestpaperWrapper();
        $scene = $this->getAnswerSceneService()->get($testpaperRecord['answer_scene_id']);
        $testpaper = $testpaperWrapper->wrapTestpaper($assessment, $scene);

        if ($testpaperRecord['user_id'] != $user['id']) {
            $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($scene['id']);
            $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper');
            $course = $this->getCourseService()->tryManageCourse($activity['fromCourseId']);
        }

        if (empty($course) && $testpaperRecord['user_id'] != $user['id']) {
            throw TestpaperException::FORBIDDEN_ACCESS_TESTPAPER();
        }

        $answerReport = $this->getAnswerReportService()->get($testpaperRecord['answer_report_id']);
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($testpaperRecord['id']);
        $testpaperWrapper = new TestpaperWrapper();
        $items = ArrayToolkit::groupIndex($testpaperWrapper->wrapTestpaperItems($assessment, $questionReports), 'type', 'id');

        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return [
            'testpaper' => $testpaper,
            'testpaperResult' => $testpaperWrapper->wrapTestpaperResult($testpaperRecord, $assessment, $scene, $answerReport),
        ];
    }

    public function get(ApiRequest $request, $resultId)
    {
        $questionSetting = $this->getSettingService()->get('questions', []);

        $answerShowMode = empty($questionSetting['testpaper_answers_show_mode']) ? 'submitted' : $questionSetting['testpaper_answers_show_mode'];

        $resultShow = true;
        // 不显示题目
        if ('hide' == $answerShowMode) {
            $resultShow = false;
        }

        $user = $this->getCurrentUser();
        $testpaperRecord = $this->getAnswerRecordService()->get($resultId);
        if (!$testpaperRecord || $testpaperRecord['user_id'] != $user['id']) {
            throw TestpaperException::FORBIDDEN_ACCESS_TESTPAPER();
        }

        //客观题自动批阅完后先显示答案解析
        if ('reviewed' == $answerShowMode && 'finished' != $testpaperRecord['status']) {
            $resultShow = false;
        }

        $assessment = $this->getAssessmentService()->showAssessment($testpaperRecord['assessment_id']);

        $scene = $this->getAnswerSceneService()->get($testpaperRecord['answer_scene_id']);
        if ($testpaperRecord['user_id'] != $user['id']) {
            $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($scene['id']);
            $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper');
            $course = $this->getCourseService()->tryManageCourse($activity['fromCourseId']);
        }

        if (empty($course) && $testpaperRecord['user_id'] != $user['id']) {
            throw TestpaperException::FORBIDDEN_ACCESS_TESTPAPER();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($testpaperRecord['id']);
        $items = $testpaperWrapper->wrapTestpaperItems($assessment, $questionReports);
        $accuracy = $this->makeAccuracy($items, $questionReports);

        $favorites = $this->findQuestionFavorites($user['id']);

        $items = ArrayToolkit::groupIndex($items, 'type', 'id');
        $testpaper = $testpaperWrapper->wrapTestpaper($assessment, $scene);
        $testpaper['metas']['question_type_seq'] = array_keys($items);
        $answerReport = $this->getAnswerReportService()->get($testpaperRecord['answer_report_id']);
        $testpaperResult = $testpaperWrapper->wrapTestpaperResult($testpaperRecord, $assessment, $scene, $answerReport);

        return [
            'testpaper' => $testpaper,
            'items' => $items,
            'accuracy' => $accuracy,
            'testpaperResult' => $testpaperResult,
            'favorites' => ArrayToolkit::column($favorites, 'question_id'),
            'resultShow' => $resultShow,
        ];
    }

    protected function findQuestionFavorites($userId)
    {
        $count = $this->getQuestionFavoriteService()->count(['user_id' => $userId]);
        $favorites = $this->getQuestionFavoriteService()->search(['user_id' => $userId], [], 0, $count);

        return $favorites;
    }

    protected function makeAccuracy($items, $questionReports)
    {
        $accuracy = [];

        $questionReports = ArrayToolkit::index($questionReports, 'question_id');
        foreach ($items as $item) {
            $questionReport = empty($questionReports[$item['id']]) ? [] : $questionReports[$item['id']];

            if (!empty($item['subs']) || 'material' == $item['type']) {
                $accuracy['material'] = empty($accuracy['material']) ? [] : $accuracy['material'];

                $accuracy['material'] = $this->countItemResultStatus($accuracy['material'], $item, $questionReport);
            } else {
                $accuracy[$item['type']] = empty($accuracy[$item['type']]) ? [] : $accuracy[$item['type']];

                $accuracyResult = $this->countItemResultStatus($accuracy[$item['type']], $item, $questionReport);

                $accuracy[$item['type']] = $accuracyResult;
            }
        }

        return $accuracy;
    }

    protected function countItemResultStatus($resultStatus, $item, $questionResult)
    {
        $resultStatus = [
            'score' => empty($resultStatus['score']) ? 0 : $resultStatus['score'],
            'totalScore' => empty($resultStatus['totalScore']) ? 0 : $resultStatus['totalScore'],
            'all' => empty($resultStatus['all']) ? 0 : $resultStatus['all'],
            'right' => empty($resultStatus['right']) ? 0 : $resultStatus['right'],
            'partRight' => empty($resultStatus['partRight']) ? 0 : $resultStatus['partRight'],
            'wrong' => empty($resultStatus['wrong']) ? 0 : $resultStatus['wrong'],
            'noAnswer' => empty($resultStatus['noAnswer']) ? 0 : $resultStatus['noAnswer'],
        ];

        $score = empty($questionResult['score']) ? 0 : $questionResult['score'];
        $status = empty($questionResult['status']) ? 'noAnswer' : $questionResult['status'];
        $resultStatus['score'] += $score;
        $resultStatus['totalScore'] += $item['score'];

        if (empty($item['subs'])) {
            ++$resultStatus['all'];
        }

        if ('material' == $item['type']) {
            return $resultStatus;
        }

        if ('right' == $status) {
            ++$resultStatus['right'];
        }

        if ('part_right' == $status) {
            ++$resultStatus['partRight'];
        }

        if ('wrong' == $status) {
            ++$resultStatus['wrong'];
        }

        if ('no_answer' == $status) {
            ++$resultStatus['noAnswer'];
        }

        return $resultStatus;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->service('Question:QuestionService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->service('ItemBank:Item:QuestionFavoriteService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
