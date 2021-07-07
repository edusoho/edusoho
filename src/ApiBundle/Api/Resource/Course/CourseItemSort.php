<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class CourseItemSort extends AbstractResource
{
    /**
     * post /api/course/{courseId}/item_sort
     */
    public function add(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $sortInfos = $request->request->get('sortInfos');
        $sortInfos = $this->getCourseService()->courseItemIdsHandle($courseId, $sortInfos);
        $this->getCourseService()->sortCourseItems($courseId, $sortInfos);

        $this->getCourseService()->sortLiveTasksWithLiveCourse($courseId, $sortInfos);

        return ['success' => true];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getSubtitleService()
    {
        return $this->service('Subtitle:SubtitleService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->service('Activity:HomeworkActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
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
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }
}
