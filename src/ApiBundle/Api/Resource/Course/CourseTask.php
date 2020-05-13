<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Course\CourseException;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CourseTask extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        return $this->service('Task:TaskService')->findTasksByCourseId($courseId);
    }

    public function get(ApiRequest $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $this->filterActivity($activity);
        $task['activity']['finishCondition'] = $this->getActivityService()->getActivityFinishCondition($task['activity']);
        $task['result'] = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
        $task['courseUrl'] = $this->generateUrl('my_course_show', array('id' => $courseId), UrlGeneratorInterface::ABSOLUTE_URL);

        return $task;
    }

    protected function filterActivity($activity)
    {
        if ('homework' == $activity['mediaType']) {
            $homeworkActivity = $this->getHomeworkActivityService()->get($activity['mediaId']);
            $activity['mediaId'] = $homeworkActivity['assessmentId'];
        }

        if ('exercise' == $activity['mediaType']) {
            $user = $this->getCurrentUser();
            $exerciseActivity = $this->getExerciseActivityService()->getActivity($activity['mediaId']);
            $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($exerciseActivity['answerSceneId'], $user['id']);
            if (empty($answerRecord)) {
                $assessment = $this->createAssessment($activity['title'], $exerciseActivity['drawCondition']['range'], array($exerciseActivity['drawCondition']['section']));
                $activity['mediaId'] = $assessment['id'];
            } else {
                $activity['mediaId'] = $answerRecord['assessment_id'];
            }
        }

        return $activity;
    }

    protected function createAssessment($name, $range, $sections)
    {
        $sections = $this->getAssessmentService()->drawItems($range, $sections);
        $assessment = array(
            'name' => $name,
            'displayable' => 0,
            'description' => '',
            'bank_id' => $range['bank_id'],
            'sections' => $sections,
        );

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        $this->getAssessmentService()->openAssessment($assessment['id']);

        return $assessment;
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    private function getHomeworkActivityService()
    {
        return $this->service('Activity:HomeworkActivityService');
    }

    /**
     * @return ExerciseActivityService
     */
    private function getExerciseActivityService()
    {
        return $this->service('Activity:ExerciseActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }
}
