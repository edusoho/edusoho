<?php

namespace Topxia\Api\Resource;

use Biz\Activity\Service\HomeworkActivityService;
use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class HomeworkResult extends BaseResource
{
    public function post(Application $app, Request $request, $homeworkId)
    {
        //answer结构是否一致
        $answers = $request->request->all();
        $answers['usedTime'] = 0;
        $user = $this->getCurrentUser();

        $assessment = $this->getAssessmentService()->showAssessment($homeworkId);
        $homeworkActivity = $this->getHomeworkActivityService()->getByAssessmentId($assessment['id']);
        $conditions = [
            'mediaId' => $homeworkActivity['id'],
            'mediaType' => 'homework',
        ];
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);
        if (!$activities) {
            return $this->error('404', '该作业任务不存在!');
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activities[0]['fromCourseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($homeworkActivity['answerSceneId'], $user['id']);
        if (empty($answerRecord) || AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
            $answerRecord = $this->getAnswerService()->startAnswer($homeworkActivity['answerSceneId'], $assessment['id'], $user['id']);
        }

        try {
            $wrapper = new AssessmentResponseWrapper();
            $responses = $wrapper->wrap($answers, $assessment, $answerRecord);
            $this->getAnswerService()->submitAnswer($responses);
        } catch (\Exception $e) {
            return $this->error('500', $e->getMessage());
        }

        return [
            'id' => $answerRecord['id'],
        ];
    }

    public function get(Application $app, Request $request, $lessonId)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($lessonId);

        if ('homework' != $task['type']) {
            $conditions = [
                'categoryId' => $task['categoryId'],
                'type' => 'homework',
                'mode' => 'homework',
            ];
            $tasks = $this->getTaskService()->searchTasks($conditions, null, 0, 1);
            if (!$tasks) {
                return $this->error('404', '该作业不存在!');
            }

            $task = array_shift($tasks);
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $assessment = $this->getAssessmentService()->showAssessment($activity['ext']['assessmentId']);
        if (empty($assessment)) {
            return $this->error('404', '该作业不存在!');
        }

        $homeworkActivity = $this->getHomeworkActivityService()->getByAssessmentId($assessment['id']);
        $conditions = [
            'mediaId' => $homeworkActivity['id'],
            'mediaType' => 'homework',
        ];
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);
        if (!$activities) {
            return $this->error('404', '该作业任务不存在!');
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activities[0]['fromCourseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($homeworkActivity['answerSceneId'], $user['id']);

        if ('doing' === $answerRecord['status'] && ($answerRecord['user_id'] != $user['id'])) {
            return $this->error('500', '无权限访问!');
        }

        $testpaperWrapper = new TestpaperWrapper();
        $scene = $this->getAnswerSceneService()->get($homeworkActivity['answerSceneId']);
        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $homeworkResult = $testpaperWrapper->wrapTestpaperResult($answerRecord, $assessment, $scene, $answerReport);

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $items = $testpaperWrapper->wrapTestpaperItems($assessment, $questionReports);

        $homeworkResult['items'] = $this->filterItem($items, $homeworkResult['id']);

        $homeworkResult['homeworkId'] = $homeworkResult['testId'];

        //只为做兼容，app端course2.0需改
        $homeworkResult['lessonId'] = $lessonId;

        return $this->filter($homeworkResult);
    }

    private function filterItem($items, $resultId)
    {
        $newItems = [];
        foreach ($items as &$item) {
            $item = $this->filterQuestion($item, $resultId);

            if ('material' == $item['type']) {
                $subs = empty($item['subs']) ? [] : array_values($item['subs']);
                foreach ($subs as &$subItem) {
                    $subItem = $this->filterQuestion($subItem, $resultId);
                }
                $item['items'] = array_values($subs);
                unset($items['subs']);
            }

            $newItems[$item['id']] = $item;
        }

        return array_values($newItems);
    }

    protected function filterQuestion($question, $resultId)
    {
        if (isset($question['answer'])) {
            unset($question['answer']);
        }

        $question['questionParentId'] = $question['parentId'];
        $question['status'] = 'noAnswer';
        $question['score'] = '0';
        $question['resultId'] = $resultId;
        $question['teacherSay'] = null;

        $itemResult = empty($question['testResult']) ? [] : $question['testResult'];

        if ($itemResult) {
            $question['status'] = $itemResult['status'];
            $question['score'] = $itemResult['score'];
            $question['teacherSay'] = $this->filterHtml($itemResult['teacherSay']);
        }

        return $question;
    }

    public function filter($res)
    {
        $res['usedTime'] = $res['usedTime'];
        $res['updatedTime'] = date('c', $res['updateTime']);
        $res['createdTime'] = date('c', $res['beginTime']);

        return $res;
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:HomeworkActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerReportService');
    }
}
