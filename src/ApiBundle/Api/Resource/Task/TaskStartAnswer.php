<?php

namespace ApiBundle\Api\Resource\Task;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Activity\Type\Testpaper;
use Biz\Common\CommonException;
use Biz\Course\MemberException;
use Biz\Testpaper\ExerciseException;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Answer\Constant\AnswerRecordStatus;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;

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
        if (!empty($activity)) {
            $assessment = $this->getAssessmentService()->getAssessment($activity['ext']['mediaId']);
            if ('random' == $assessment['type']) {
                $ids = $this->getAssessmentService()->searchAssessments(
                    ['parent_id' => $assessment['id']],
                    ['id' => 'ASC'],
                    0,
                    PHP_INT_MAX,
                    ['id']
                );
                $ids = array_column($ids, 'id');
                $activity['ext']['mediaId'] = $ids[array_rand($ids)];
            }
        }
        $answerRecord = $this->$method($task, $activity, $request->getHttpRequest());

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        if ('open' !== $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
        }
        $assessment = $this->getAnswerRandomSeqService()->shuffleItemsAndOptionsIfNecessary($assessment, $answerRecord['id']);

        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        return [
            'assessment' => $assessment,
            'assessment_response' => $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']),
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
        ];
    }

    protected function startHomeWork($task, $activity, $request)
    {
        $latestAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $this->getCurrentUser()['id']);
        if (empty($latestAnswerRecord) || AnswerRecordStatus::FINISHED == $latestAnswerRecord['status']) {
            return $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $activity['ext']['assessmentId'], $this->getCurrentUser()['id']);
        } else {
            return $latestAnswerRecord;
        }
    }

    protected function startTestpaper($task, $activity, $request)
    {
        if ($activity['startTime'] > time()) {
            throw TestpaperException::EXAM_NOT_START();
        }
        if (Testpaper::VALID_PERIOD_MODE_RANGE == $activity['ext']['validPeriodMode'] && $activity['endTime'] < time()) {
            throw TestpaperException::END_OF_EXAM();
        }

        if (0 == $activity['ext']['remainderDoTimes'] && '1' == $activity['ext']['isLimitDoTimes']) {
            throw TestpaperException::NO_DO_TIMES();
        }

        $latestAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $this->getCurrentUser()['id']);
        if (empty($latestAnswerRecord) || AnswerRecordStatus::FINISHED == $latestAnswerRecord['status']) {
            return $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $activity['ext']['mediaId'], $this->getCurrentUser()['id']);
        } else {
            return $latestAnswerRecord;
        }
    }

    protected function startExercise($task, $activity, $request)
    {
        $exerciseMode = $request->request->get('exerciseMode', ExerciseMode::SUBMIT_ALL);
        $assessmentId = $request->request->get('assessmentId');

        $latestAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $this->getCurrentUser()['id']);
        if (!empty($latestAnswerRecord) && AnswerRecordStatus::FINISHED != $latestAnswerRecord['status']) {
            if ($latestAnswerRecord['exercise_mode'] == $exerciseMode) {
                return $latestAnswerRecord;
            }
            throw ExerciseException::EXERCISE_IS_DOING();
        }
        if (!empty($latestAnswerRecord) && $assessmentId == $latestAnswerRecord['assessment_id']) {
            try {
                $assessment = $this->getExerciseActivityService()->createExerciseAssessment($activity);
                $assessmentId = $assessment['id'];
            } catch (ItemException $e) {
                if (ErrorCode::ITEM_NOT_ENOUGH == $e->getCode()) {
                    throw ExerciseException::LACK_QUESTION();
                }
            }
        }

        if (empty($assessmentId)) {
            $assessment = $this->getExerciseActivityService()->createExerciseAssessment($activity);
            $assessmentId = $assessment['id'];
        }

        if (!$this->getExerciseActivityService()->isExerciseAssessment($assessmentId, $activity['ext'])) {
            throw ExerciseException::EXERCISE_NOTDO();
        }

        $answerRecord = $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $assessmentId, $this->getCurrentUser()['id']);

        return $this->getAnswerRecordService()->update($answerRecord['id'], ['exercise_mode' => $exerciseMode]);
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

    /**
     * @return AnswerRandomSeqService
     */
    protected function getAnswerRandomSeqService()
    {
        return $this->service('ItemBank:Answer:AnswerRandomSeqService');
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->service('Activity:ExerciseActivityService');
    }
}
