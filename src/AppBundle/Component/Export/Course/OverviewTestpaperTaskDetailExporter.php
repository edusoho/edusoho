<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewTestpaperTaskDetailExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        $task = $this->getTaskService()->getTask($this->parameter['courseTaskId']);

        try {
            $tryManageCourse = $this->getCourseService()->tryManageCourse($task['courseId']);
        } catch (\Exception $e) {
            return false;
        }

        return $user->isAdmin() || !empty($tryManageCourse);
    }

    public function getCount()
    {
        return $this->getTaskResultService()->countTaskResults($this->conditions);
    }

    public function getTitles()
    {
        return [
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.createdTime',
            'course.task.finish_time',
            'task.learn_data_detail.testpaper_firstUsedTime',
            'task.learn_data_detail.testpaper_firstScore',
            'task.learn_data_detail.testpaper_maxScore',
        ];
    }

    public function getContent($start, $limit)
    {
        $task = $this->getTaskService()->getTask($this->parameter['courseTaskId']);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $this->conditions,
            ['createdTime' => 'ASC'],
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $testpaperResults = $this->getTestpaperResults($activity, $userIds);

        $datas = [];

        foreach ($taskResults as $taskResult) {
            $user = $users[$taskResult['userId']];
            $testpaperResult = empty($testpaperResults[$taskResult['userId']]) ? [] : $testpaperResults[$taskResult['userId']];
            $data = [];
            $data[] = $user['nickname'];
            $data[] = empty($taskResult['createdTime']) ? '-' : date('Y-m-d H:i:s', $taskResult['createdTime']);
            $data[] = empty($taskResult['finishedTime']) ? '-' : date('Y-m-d H:i:s', $taskResult['finishedTime']);
            $data[] = empty($testpaperResult['usedTime']) ? '-' : $testpaperResult['usedTime'];
            $data[] = empty($testpaperResult['firstScore']) ? '-' : $testpaperResult['firstScore'];
            $data[] = empty($testpaperResult['maxScore']) ? '-' : $testpaperResult['maxScore'];

            $datas[] = $data;
        }

        return $datas;
    }

    protected function getTestpaperResults($activity, $userIds)
    {
        $testpaperResults = [];
        $answerRecords = $this->getAnswerRecords($activity['ext']['answerScene']['id'], $userIds);

        foreach ($answerRecords as $userId => $userAnswerRecords) {
            $userFirstRecord = $userAnswerRecords[0];
            $scores = ArrayToolkit::column($userAnswerRecords, 'score');
            $testpaperResults[$userId] = [
                'usedTime' => round($userFirstRecord['used_time'] / 60, 1),
                'firstScore' => $userFirstRecord['score'],
                'maxScore' => max($scores),
            ];
        }

        return $testpaperResults;
    }

    protected function getAnswerRecords($answerSceneId, $userIds)
    {
        $answerReports = $this->getAnswerReportService()->search(
            ['answer_scene_id' => $answerSceneId],
            [],
            0,
            $this->getAnswerReportService()->count(['answer_scene_id' => $answerSceneId]),
            ['score', 'user_id', 'answer_record_id']
        );
        $answerReports = ArrayToolkit::index($answerReports, 'answer_record_id');

        $conditions = [
            'answer_scene_id' => $answerSceneId,
            'user_ids' => $userIds,
            'status' => 'finished',
        ];
        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            [],
            0,
            $this->getAnswerRecordService()->count($conditions),
            ['user_id', 'used_time', 'id']
        );
        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['score'] = $answerReports[$answerRecord['id']]['score'];
        }
        $answerRecords = ArrayToolkit::group($answerRecords, 'user_id');

        return $answerRecords;
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseTaskId'] = $conditions['courseTaskId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return ArrayToolkit::parts($conditions, ['courseTaskId']);
    }

    protected function getReportService()
    {
        return $this->getBiz()->service('Course:ReportService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
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
