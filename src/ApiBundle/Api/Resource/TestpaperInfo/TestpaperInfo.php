<?php

namespace ApiBundle\Api\Resource\TestpaperInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Testpaper\TestpaperException;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class TestpaperInfo extends AbstractResource
{
    public function get(ApiRequest $request, $testId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw UserException::UN_LOGIN();
        }

        $assessment = $this->getAssessmentService()->showAssessment($testId);

        if (empty($assessment)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $testpaper = $testpaperWrapper->wrapTestpaper($assessment);
        $items = ArrayToolkit::groupIndex($testpaperWrapper->wrapTestpaperItems($assessment), 'type', 'id');
        $testpaper['metas']['question_type_seq'] = array_keys($items);
        $results = [
            'testpaper' => $testpaper,
            'items' => $this->filterTestpaperItems($items),
        ];

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');
        if (empty($targetType) || empty($targetId)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $method = 'handle'.$targetType;
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }
        $this->$method($user, $targetId, $results);

        return $results;
    }

    protected function handleTask($user, $taskId, &$results)
    {
        $task = $this->getTaskService()->tryTakeTask($taskId);
        if (empty($task)) {
            throw TaskException::NOTFOUND_TASK();
        }
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if ('testpaper' != $activity['mediaType']) {
            throw TestpaperException::NOT_TESTPAPER_TASK();
        }

        $assessment = $this->getAssessmentService()->showAssessment($activity['ext']['mediaId']);
        if (empty($assessment)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $testpaper = $testpaperWrapper->wrapTestpaper($assessment);
        $items = ArrayToolkit::groupIndex($testpaperWrapper->wrapTestpaperItems($assessment), 'type', 'id');
        $testpaper['metas']['question_type_seq'] = array_keys($items);
        $results = [
            'testpaper' => $testpaper,
            'items' => $this->filterTestpaperItems($items),
        ];

        $scene = $this->getAnswerSceneService()->get($activity['ext']['answerSceneId']);
        $testpaperRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $user['id']);

        if (!empty($testpaperRecord)) {
            $answerReport = $this->getAnswerReportService()->get($testpaperRecord['answer_report_id']);
            $testpaperWrapper = new TestpaperWrapper();
            $testpaperResult = $testpaperWrapper->wrapTestpaperResult($testpaperRecord, $testpaper, $scene, $answerReport);
            $testpaperResult['courseId'] = $task['courseId'];
            $testpaperResult['lessonId'] = $task['id'];
            $results['testpaperResult'] = $testpaperResult;
        }

        $task['activity'] = $activity;

        $results['testpaper']['limitedTime'] = empty($scene['limited_time']) ? '0' : $scene['limited_time'];
        $results['task'] = $this->filterFaceInspectionTask($task, $scene);
    }

    private function filterTestpaperItems($items)
    {
        $itemArray = [];

        foreach ($items as $questionType => $item) {
            $itemArray[$questionType] = count($item);
        }

        return $itemArray;
    }

    private function filterFaceInspectionTask($task, $scene)
    {
        if (!empty($scene['enable_facein'])) {
            $task['enable_facein'] = $scene['enable_facein'];
        }

        return $task;
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
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
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }
}
