<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class TestpaperReviewRoleDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $answerSceneId = 0;
        if (isset($arguments['answerRecordId'])) {
            $answerRecord = $this->getAnswerRecordService()->get($arguments['answerRecordId']);
            $answerSceneId = $answerRecord['answer_scene_id'];
        }
        if (isset($arguments['answerSceneId'])) {
            $answerSceneId = $arguments['answerSceneId'];
        }

        $task = $this->getTaskByAnswerSceneId($answerSceneId);

        if (empty($task)) {
            return false;
        }

        return $this->getCourseService()->hasCourseManagerRole($task['courseId']);
    }

    protected function getTaskByAnswerSceneId($answerSceneId)
    {
        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerSceneId);
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper');

        return $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getServiceKernel()->getBiz()->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getServiceKernel()->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getServiceKernel()->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:CourseService');
    }
}
