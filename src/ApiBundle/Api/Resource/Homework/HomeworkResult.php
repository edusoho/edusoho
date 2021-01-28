<?php

namespace ApiBundle\Api\Resource\Homework;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Testpaper\HomeworkException;
use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class HomeworkResult extends AbstractResource
{
    public function add(ApiRequest $request, $homeworkId)
    {
        $user = $this->getCurrentUser();

        $targetType = $request->request->get('targetType');
        $targetId = $request->request->get('targetId');

        $homework = $this->getAssessmentService()->showAssessment($homeworkId);
        if (empty($homework) || '0' != $homework['displayable']) {
            throw HomeworkException::NOTFOUND_HOMEWORK();
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);
        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $homeworkRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $user['id']);

        if (empty($homeworkRecord) || 'finished' == $homeworkRecord['status']) {
            if ('draft' == $homework['status']) {
                throw HomeworkException::DRAFT_HOMEWORK();
            }
            if ('closed' == $homework['status']) {
                throw HomeworkException::CLOSED_HOMEWORK();
            }

            $homeworkRecord = $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $homework['id'], $user['id']);
        } elseif ('reviewing' == $homeworkRecord['status']) {
            throw HomeworkException::REVIEWING_HOMEWORK();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $scene = $this->getAnswerSceneService()->get($homeworkRecord['answer_scene_id']);
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($homeworkRecord['id']);
        $answerReport = $this->getAnswerReportService()->get($homeworkRecord['answer_report_id']);
        $homeworkResult = $testpaperWrapper->wrapTestpaperResult($homeworkRecord, $homework, $scene, $answerReport);
        $homeworkResult['items'] = array_values($testpaperWrapper->wrapTestpaperItems($homework, $questionReports));

        return $homeworkResult;
    }

    public function update(ApiRequest $request, $homeworkId, $homeworkResultId)
    {
        $user = $this->getCurrentUser();

        $data = $request->request->all();
        $homeworkRecord = $this->getAnswerRecordService()->get($homeworkResultId);

        if (!empty($homeworkRecord) && !in_array($homeworkRecord['status'], ['doing', 'paused'])) {
            throw HomeworkException::FORBIDDEN_DUPLICATE_COMMIT();
        }

        $wrapper = new AssessmentResponseWrapper();
        $assessment = $this->getAssessmentService()->showAssessment($homeworkRecord['assessment_id']);
        $assessmentResponse = $wrapper->wrap($data, $assessment, $homeworkRecord);
        $homeworkRecord = $this->getAnswerService()->submitAnswer($assessmentResponse);
        $scene = $this->getAnswerSceneService()->get($homeworkRecord['answer_scene_id']);

        if ($homeworkRecord['user_id'] != $user['id']) {
            $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($scene['id']);
            $activity = $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'homework');
            $course = $this->getCourseService()->tryManageCourse($activity['fromCourseId']);
        }

        if (empty($course) && $homeworkRecord['user_id'] != $user['id']) {
            throw HomeworkException::FORBIDDEN_ACCESS_HOMEWORK();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $answerReport = $this->getAnswerReportService()->get($homeworkRecord['answer_report_id']);

        return $testpaperWrapper->wrapTestpaperResult($homeworkRecord, $assessment, $scene, $answerReport);
    }

    public function get(ApiRequest $request, $homeworkId, $homeworkResultId)
    {
        $user = $this->getCurrentUser();

        $homeworkRecord = $this->getAnswerRecordService()->get($homeworkResultId);
        if (empty($homeworkRecord)) {
            throw HomeworkException::NOTFOUND_RESULT();
        }

        $homework = $this->getAssessmentService()->showAssessment($homeworkRecord['assessment_id']);
        if (empty($homework)) {
            throw HomeworkException::NOTFOUND_HOMEWORK();
        }

        $scene = $this->getAnswerSceneService()->get($homeworkRecord['answer_scene_id']);
        $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($scene['id']);
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'homework');
        $canTakeCourse = $this->getCourseService()->canTakeCourse($activity['fromCourseId']);
        if (!$canTakeCourse) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        if ('doing' === $homeworkRecord['status'] && ($homeworkRecord['user_id'] != $user['id'])) {
            throw HomeworkException::FORBIDDEN_ACCESS_HOMEWORK();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($homeworkRecord['id']);
        $answerReport = $this->getAnswerReportService()->get($homeworkRecord['answer_report_id']);
        $homeworkResult = $testpaperWrapper->wrapTestpaperResult($homeworkRecord, $homework, $scene, $answerReport);
        $homeworkResult['items'] = array_values($testpaperWrapper->wrapTestpaperItems($homework, $questionReports));
        $homeworkResult['items'] = $this->fillItems($homeworkResult['items'], $questionReports);
        $homeworkResult['rightRate'] = $this->getRightRate($homeworkResult['items']);

        return $homeworkResult;
    }

    protected function getRightRate($items)
    {
        $rightNum = $num = 0;

        foreach ($items as $item) {
            ++$num;

            if (isset($item['testResult']) && 'right' == $item['testResult']['status']) {
                ++$rightNum;
            }

            if ('material' == $item['type'] && !empty($item['subs'])) {
                --$num;
                foreach ($item['subs'] as $subItem) {
                    ++$num;

                    if (isset($subItem['testResult']) && 'right' == $subItem['testResult']['status']) {
                        ++$rightNum;
                    }
                }
            }
        }

        return intval($rightNum / $num * 100 + 0.5);
    }

    protected function fillItems($items, $questionReports)
    {
        $questionReports = ArrayToolkit::index($questionReports, 'question_id');
        foreach ($items as &$item) {
            if (isset($item['subs'])) {
                foreach ($item['subs'] as &$sub) {
                    $sub['testResult'] = isset($questionReports[$sub['id']]) ? $sub['testResult'] : null;
                }
            } else {
                $item['testResult'] = isset($questionReports[$item['id']]) ? $item['testResult'] : null;
            }
        }

        return $items;
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
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
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->service('Activity:HomeworkActivityService');
    }
}
