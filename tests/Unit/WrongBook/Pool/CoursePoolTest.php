<?php

namespace Tests\Unit\WrongBook\Pool;

use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class CoursePoolTest extends BaseTestCase
{
    public function testPrepareSceneIds()
    {
        $pool = $this->createPool();
        $this->createActivityWithMedia(1, ['mediaType' => 'homework', 'fromCourseSetId' => 1, 'fromCourseId' => 2]);
        $this->createActivityWithMedia(2, ['mediaType' => 'testpaper', 'fromCourseSetId' => 1, 'fromCourseId' => 3]);
        $coursePool = $this->biz['wrong_question.course_pool'];
        $sceneIds = $coursePool->prepareSceneIds($pool['id'], ['courseId' => 2, 'courseMediaType' => 'homework']);

        $this->assertEquals([1], array_values($sceneIds));
    }

    public function testPrepareSceneIdsByTargetId()
    {
        $this->createCourse(1, 1);
        $this->createActivityWithMedia(1, ['mediaType' => 'homework', 'fromCourseSetId' => 1, 'fromCourseId' => 1]);
        $this->createActivityWithMedia(2, ['mediaType' => 'testpaper', 'fromCourseSetId' => 1, 'fromCourseId' => 1]);
        $this->createActivityWithMedia(3, ['mediaType' => 'exercise', 'fromCourseSetId' => 1, 'fromCourseId' => 1]);

        $coursePool = $this->biz['wrong_question.course_pool'];
        $sceneIds = array_values($coursePool->prepareSceneIdsByTargetId(1, ['courseId' => 1]));
        sort($sceneIds);
        $this->assertEquals([1, 2, 3], $sceneIds);
    }

    protected function createCourse($courseId, $courseSetId)
    {
        $course = [
            'id' => $courseId,
            'title' => 'course title',
            'courseSetId' => $courseSetId,
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => 1,
            'courseType' => 'normal',
        ];

        $this->getCourseService()->createCourse($course);
    }

    protected function createPool($poolFields = [])
    {
        $pool = array_merge([
            'user_id' => 1,
            'item_num' => 1,
            'target_type' => 'course',
            'target_id' => 1,
        ], $poolFields);

        return $this->getWrongQuestionBookPoolDao()->create($pool);
    }

    protected function createActivityWithMedia($answerSceneId, $activityFields = [])
    {
        $activity = [
            'title' => 'test activity',
            'mediaId' => 1,
            'mediaType' => 'testpaper',
            'fromCourseId' => 2,
            'fromCourseSetId' => 1,
        ];

        $activity = array_merge($activity, $activityFields);
        $this->getActivityDao()->create($activity);
        $typeMethod = 'createActivity'.ucfirst($activity['mediaType']);

        if (method_exists($this, $typeMethod)) {
            $this->$typeMethod($activity['mediaId'], $answerSceneId);
        }
    }

    protected function createActivityTestpaper($id, $answerSceneId)
    {
        $activityTestpaper = [
            'id' => $id,
            'answerSceneId' => $answerSceneId,
        ];
        $this->getTestpaperActivityService()->createActivity($activityTestpaper);
    }

    protected function createActivityHomework($id, $answerSceneId)
    {
        $activityHomework = [
            'id' => $id,
            'answerSceneId' => $answerSceneId,
        ];
        $this->getHomeworkActivityService()->create($activityHomework);
    }

    protected function createActivityExercise($id, $answerSceneId)
    {
        $activityExercise = [
            'id' => $id,
            'answerSceneId' => $answerSceneId,
        ];
        $this->getExerciseActivityService()->createActivity($activityExercise);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->createDao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->createService('Activity:HomeworkActivityService');
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->createService('Activity:ExerciseActivityService');
    }
}
