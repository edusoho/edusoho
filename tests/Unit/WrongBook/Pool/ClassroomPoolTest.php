<?php

namespace Tests\Unit\WrongBook\Pool;

use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\BaseTestCase;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class ClassroomPoolTest extends BaseTestCase
{
    public function testPrepareSceneIds()
    {
        $pool = $this->createPool();
        $this->createClassroomAndCourse();
        $this->createActivityWithMedia(1, ['mediaType' => 'homework', 'fromCourseSetId' => 1, 'fromCourseId' => 1]);
        $this->createActivityWithMedia(2, ['mediaType' => 'testpaper', 'fromCourseSetId' => 1, 'fromCourseId' => 2]);
        $coursePool = $this->biz['wrong_question.classroom_pool'];
        $sceneIds = $coursePool->prepareSceneIds($pool['id'], ['classroomCourseSetId' => 1, 'classroomMediaType' => 'homework']);

        $this->assertEquals([1], array_values($sceneIds));
    }

    public function testPrepareSceneIdsByTargetId()
    {
        $this->createClassroomAndCourse();
        $this->createActivityWithMedia(1, ['mediaType' => 'homework', 'fromCourseSetId' => 1, 'fromCourseId' => 1]);
        $this->createActivityWithMedia(2, ['mediaType' => 'testpaper', 'fromCourseSetId' => 1, 'fromCourseId' => 2]);
        $this->createActivityWithMedia(3, ['mediaType' => 'testpaper', 'fromCourseSetId' => 1, 'fromCourseId' => 2, 'mediaId' => 2]);
        $coursePool = $this->biz['wrong_question.classroom_pool'];
        $sceneIds = array_values($coursePool->prepareSceneIdsByTargetId(1, ['classroomMediaType' => 'testpaper']));
        sort($sceneIds);
        $this->assertEquals([2, 3], $sceneIds);
    }

    protected function createClassroomAndCourse()
    {
        $classroom = $this->getClassroomService()->addClassroom(['title' => 'test']);

        $this->createCourse(1, 1);
        $this->createCourse(2, 1);

        $classroomCourse1 = [
                'classroomId' => $classroom['id'],
                'courseId' => 1,
                'parentCourseId' => 1,
                'courseSetId' => 1,
        ];
        $classroomCourse2 = [
            'classroomId' => $classroom['id'],
            'courseId' => 2,
            'parentCourseId' => 1,
            'courseSetId' => 1,
        ];
        $this->getClassroomCourseDao()->create($classroomCourse1);
        $this->getClassroomCourseDao()->create($classroomCourse2);
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
            'target_type' => 'classroom',
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
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->createDao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return ClassroomCourseDao
     */
    protected function getClassroomCourseDao()
    {
        return $this->createDao('Classroom:ClassroomCourseDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
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
