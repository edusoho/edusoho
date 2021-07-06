<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class MultiClassStudentExamResult extends AbstractResource
{
    public function search(ApiRequest $request, $multiClassId, $studentId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        if (!$this->getCourseService()->hasCourseManagerRole($multiClass['courseId'])) {
            throw CourseException::FORBIDDEN_MANAGE_COURSE();
        }

        $type = $request->query->get('type', '');

        if (!in_array($type, ['homework', 'testpaper'])) {
            throw CommonException::ERROR_PARAMETER();
        }

        $courseId = $multiClass['courseId'];

        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, $type, true);
        $answerSceneIds = [];
        $sceneIndexActivities = [];
        foreach ($activities as $activity) {
            $answerSceneIds[] = $activity['ext']['answerSceneId'];
            $sceneIndexActivities[$activity['ext']['answerSceneId']] = $activity;
        }

        $status = $request->query->get('status', 'all');
        if (!in_array($status, ['all', 'finished', 'reviewing', 'doing'])) {
            $status = 'all';
        }

        $conditions = ['answer_scene_ids' => empty($answerSceneIds) ? [-1] : $answerSceneIds, 'user_id' => $studentId];

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
        $answerRecords = $this->filterRecords($answerRecords, $sceneIndexActivities);

        return $this->makePagingObject($answerRecords, $total, $offset, $limit);
    }

    protected function filterRecords($answerRecords, $sceneIndexActivities)
    {
        $answerReports = ArrayToolkit::index($this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')), 'id');
        $studentIds = ArrayToolkit::column($answerRecords, 'user_id');
        $teacherIds = ArrayToolkit::column($answerReports, 'review_user_id');
        $userIds = array_merge($studentIds, $teacherIds);
        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filters($users);

        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['answerScene'] = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
            $answerRecord['activity'] = $sceneIndexActivities[$answerRecord['answer_scene_id']];
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

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
