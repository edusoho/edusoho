<?php

namespace ApiBundle\Api\Resource\Task;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\MemberException;
use Biz\Common\CommonException;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;

class TaskStartAnswer extends AbstractResource
{
    public function add(ApiRequest $request, $taskId)
    {
        $canLearn = $this->getCourseService()->canLearnTask($taskId);
        if ('success' != $canLearn['code']) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $method = 'start'.ucfirst($activity['mediaType']);
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        $answerRecord = $this->$method($task, $activity);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        return array(
            'assessment' => $assessment,
            'assessment_response' => $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']),
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
        );
    }

    protected function startHomeWork($task, $activity)
    {
        return $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $activity['ext']['assessmentId'], $this->getCurrentUser()['id']);
    }

    protected function startTestpaper($task, $activity)
    {
        return $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $activity['ext']['mediaId'], $this->getCurrentUser()['id']);
    }

    protected function startExercise($task, $activity)
    {
        $latestAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $this->getCurrentUser()['id']);
        if (empty($latestAnswerRecord) || 'finished' == $latestAnswerRecord['status']) {
            $assessment = $this->createExerciseAssessment($activity);

            return $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $assessment['id'], $this->getCurrentUser()['id']);
        } else {
            return $latestAnswerRecord;
        }
    }

    protected function createExerciseAssessment($activity)
    {
        $range = $activity['ext']['drawCondition']['range'];
        $sections = $this->getAssessmentService()->drawItems(
            $range,
            array($activity['ext']['drawCondition']['section'])
        );

        $assessment = array(
            'name' => $activity['title'],
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
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return \Biz\Task\Service\TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return \Biz\Activity\Service\ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService;
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }
}
