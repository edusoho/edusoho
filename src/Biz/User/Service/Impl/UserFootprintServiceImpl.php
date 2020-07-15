<?php

namespace Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\User\Dao\UserFootprintDao;
use Biz\User\Service\UserFootprintService;

class UserFootprintServiceImpl extends BaseService implements UserFootprintService
{
    public function createUserFootprint($footprint)
    {
        $footprint = $this->checkAndFilterFootprint($footprint);

        $conditions = $footprint;
        $existedFootprint = $this->searchUserFootprints($conditions, [], 0, 1);

        if (empty($existedFootprint)) {
            return $this->getUserFootprintDao()->create($footprint);
        }

        return $this->updateFootprint($existedFootprint[0]['id'], $footprint);
    }

    public function updateFootprint($id, $footprint)
    {
        $footprint = $this->checkAndFilterFootprint($footprint);

        return $this->getUserFootprintDao()->update($id, $footprint);
    }

    public function searchUserFootprints(array $conditions, array $order, $start, $limit, $columns = [])
    {
        return $this->getUserFootprintDao()->search($conditions, $order, $start, $limit, $columns);
    }

    public function countUserFootprints($conditions)
    {
        return $this->getUserFootprintDao()->count($conditions);
    }

    public function prepareUserFootprintsByType($footprints, $type)
    {
        if (empty($footprints)) {
            return [];
        }

        if (in_array($type, array_keys(UserFootprintService::PREPARE_METHODS))) {
            $method = UserFootprintService::PREPARE_METHODS[$type];

            return $this->$method($footprints);
        }

        return $footprints;
    }

    public function deleteUserFootprintsBeforeDate($date)
    {
        return $this->getUserFootprintDao()->deleteBeforeDate($date);
    }

    protected function prepareItemBankAssessmentExerciseFootprints($footprints)
    {
        $assessmentExerciseRecords = $modules = $assessments = $exercises = [];

        $assessmentExerciseRecords = ArrayToolkit::index($this->getItemBankAssessmentExerciseRecordService()->search(
            ['assessmentExerciseIds' => ArrayToolkit::column($footprints, 'targetId')],
            [],
            0,
            PHP_INT_MAX
        ), 'assessmentExerciseId');

        if ($assessmentExerciseRecords) {
            $modules = ArrayToolkit::index($this->getItemBankExerciseModuleService()->search(
                ['ids' => ArrayToolkit::column($assessmentExerciseRecords, 'moduleId')],
                [],
                0,
                PHP_INT_MAX
            ), 'id');
            $exercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($assessmentExerciseRecords, 'exerciseId'));
            $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($assessmentExerciseRecords, 'assessmentId'));

            foreach ($footprints as &$footprint) {
                $assessmentExerciseRecord = empty($assessmentExerciseRecords[$footprint['targetId']]) ? (object) [] : $assessmentExerciseRecords[$footprint['targetId']];
                $footprint['target'] = [
                    'assessment' => empty($assessments[$assessmentExerciseRecord['assessmentId']]) ? (object) [] : $assessments[$assessmentExerciseRecord['assessmentId']],
                    'module' => empty($modules[$assessmentExerciseRecord['moduleId']]) ? (object) [] : $modules[$assessmentExerciseRecord['moduleId']],
                    'answerRecord' => $assessmentExerciseRecord,
                    'exercise' => empty($exercises[$assessmentExerciseRecord['exerciseId']]) ? (object) [] : $exercises[$assessmentExerciseRecord['exerciseId']],
                ];
            }
        }

        return $footprints;
    }

    protected function prepareItemBankChapterExerciseFootprints($footprints)
    {
        $chapterExerciseRecords = $modules = $assessments = $exercises = [];

        $chapterExerciseRecords = ArrayToolkit::index($this->getItemBankChapterExerciseRecordService()->search(
            ['itemCategoryIds' => ArrayToolkit::column($footprints, 'targetId')],
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        ), 'itemCategoryId');

        if ($chapterExerciseRecords) {
            $modules = ArrayToolkit::index($this->getItemBankExerciseModuleService()->search(
                ['ids' => ArrayToolkit::column($chapterExerciseRecords, 'moduleId')],
                [],
                0,
                PHP_INT_MAX
            ), 'id');
            $itemCategories = $this->getItemCategoryService()->findItemCategoriesByIds(ArrayToolkit::column($chapterExerciseRecords, 'itemCategoryId'));
            $exercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($chapterExerciseRecords, 'exerciseId'));
            $answerRecrods = ArrayToolkit::index($this->getAnswerRecordService()->search(['ids' => ArrayToolkit::column($chapterExerciseRecords, 'answerRecordId')], [], 0, PHP_INT_MAX), 'id');
            $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecrods, 'assessment_id'));

            foreach ($footprints as &$footprint) {
                $chapterExerciseRecord = empty($chapterExerciseRecords[$footprint['targetId']]) ? (object) [] : $chapterExerciseRecords[$footprint['targetId']];
                $footprint['target'] = [
                    'assessment' => empty($assessments[$answerRecrods[$chapterExerciseRecord['answerRecordId']]['assessment_id']]) ? (object) [] : $assessments[$answerRecrods[$chapterExerciseRecord['answerRecordId']]['assessment_id']],
                    'module' => empty($modules[$chapterExerciseRecord['moduleId']]) ? (object) [] : $modules[$chapterExerciseRecord['moduleId']],
                    'answerRecord' => $chapterExerciseRecord,
                    'exercise' => empty($exercises[$chapterExerciseRecord['exerciseId']]) ? (object) [] : $exercises[$chapterExerciseRecord['exerciseId']],
                    'itemCategory' => empty($itemCategories[$chapterExerciseRecord['itemCategoryId']]) ? (object) [] : $itemCategories[$chapterExerciseRecord['itemCategoryId']],
                ];
            }
        }

        return $footprints;
    }

    protected function prepareTaskFootprints($footprints)
    {
        $taskIds = ArrayToolkit::column($footprints, 'targetId');

        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        if (empty($tasks)) {
            return $footprints;
        }

        $tasks = ArrayToolkit::index($tasks, 'id');
        $courseIds = ArrayToolkit::column($tasks, 'courseId');

        $courses = $this->getCourseService()->searchCourses(['ids' => $courseIds], [], 0, count($courseIds), ['id', 'title', 'courseSetTitle']);
        $courses = ArrayToolkit::index($courses, 'id');

        $classroomCourses = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
        $classroomCourses = ArrayToolkit::index($classroomCourses, 'courseId');
        $classroomIds = ArrayToolkit::column($classroomCourses, 'classroomId');

        $activities = $this->getActivityService()->findActivities(ArrayToolkit::column($tasks, 'activityId'), true);
        $activities = ArrayToolkit::index($activities, 'id');

        $classrooms = $this->getClassroomService()->searchClassrooms(['classroomIds' => $classroomIds], [], 0, count($classroomIds), ['id', 'title']);
        $classrooms = ArrayToolkit::index($classrooms, 'id');

        foreach ($footprints as &$footprint) {
            $task = empty($tasks[$footprint['targetId']]) ? null : $tasks[$footprint['targetId']];
            if (empty($task)) {
                continue;
            }

            $course = empty($courses[$task['courseId']]) ? null : $courses[$task['courseId']];
            $classroom = empty($classroomCourses[$task['courseId']]['classroomId']) || empty($classrooms[$classroomCourses[$task['courseId']]['classroomId']]) ? null : $classrooms[$classroomCourses[$task['courseId']]['classroomId']];
            $activity = empty($activities[$task['activityId']]) ? null : $activities[$task['activityId']];

            if ($task['isLesson']) {
                $task['task'] = $task;
                $task['activity'] = $activity;
                $task['course'] = $course;
                $task['classroom'] = $classroom;
                $footprint['target'] = $task;
            } else {
                $lesson = $this->getTaskService()->searchTasks(['categoryId' => $task['categoryId'], 'isLesson' => 1], [], 0, 1);
                $lesson = array_pop($lesson);
                $lesson['task'] = $task;
                $lesson['course'] = $course;
                $lesson['classroom'] = $classroom;
                $lesson['activity'] = $activity;

                $footprint['target'] = $lesson;
            }
        }

        return $footprints;
    }

    protected function checkAndFilterFootprint($footprint)
    {
        if (!ArrayToolkit::requireds($footprint, ['userId', 'targetType', 'targetId', 'event'])) {
            throw $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!empty($footprint['date']) && !SimpleValidator::date($footprint['date'])) {
            throw $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return [
            'userId' => $footprint['userId'],
            'targetType' => $footprint['targetType'],
            'targetId' => $footprint['targetId'],
            'event' => $footprint['event'],
            'date' => empty($footprint['date']) ? date('Y-m-d', time()) : $footprint['date'],
        ];
    }

    /**
     * @return UserFootprintDao
     */
    protected function getUserFootprintDao()
    {
        return $this->createDao('User:UserFootprintDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
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
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }
}
