<?php


namespace ApiBundle\Api\Resource\MultiClass;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class MultiClassTaskExamResult extends AbstractResource
{
    public function search(ApiRequest $request, $multiClassId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if (!in_array($activity['mediaType'], ['homework', 'testpaper'])){
            throw CommonException::ERROR_PARAMETER();
        }
        $answerScene = $this->getAnswerSceneService()->get($activity['ext']['answerSceneId']);

        $status = $request->query->get('status', 'finished');
        if (!in_array($status, ['all', 'finished', 'reviewing', 'doing'])) {
            $status = 'all';
        }

        $conditions = ['answer_scene_id' => $answerScene['id']];
        if ('all' !== $status) {
            $conditions['status'] = $status;
        }

        $total = $this->getAnswerRecordService()->count($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $orderBy = in_array($status, ['reviewing', 'finished']) ? ['end_time' => 'ASC'] : ['updated_time' => 'DESC'];
        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            $orderBy,
            $offset,
            $limit
        );
        $answerRecords = $this->filterRecords($answerRecords);

        return $this->makePagingObject($answerRecords, $total, $offset, $limit);
    }

    protected function filterRecords($answerRecords)
    {
        $answerReports = ArrayToolkit::index($this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')), 'id');
        $studentIds = ArrayToolkit::column($answerRecords, 'user_id');
        $teacherIds = ArrayToolkit::column($answerReports, 'review_user_id');
        $userIds = array_merge($studentIds, $teacherIds);
        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filters($users);

        foreach ($answerRecords as &$answerRecord){
            $answerRecord['answerReportInfo'] = isset($answerReports[$answerRecord['answer_report_id']]) ? $answerReports[$answerRecord['answer_report_id']] : [];
            $answerRecord['userInfo'] = isset($users[$answerRecord['user_id']]) ? $users[$answerRecord['user_id']] : [];
            $answerRecord['teacherInfo'] = isset($users[$answerRecord['answerReportInfo']['review_user_id']]) ? $users[$answerRecord['answerReportInfo']['review_user_id']] : [];
        }

        return $answerRecords;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
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
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}