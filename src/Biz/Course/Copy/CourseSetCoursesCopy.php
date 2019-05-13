<?php

namespace Biz\Course\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Dao\QuestionDao;
use Biz\Task\Dao\TaskDao;
use Biz\Testpaper\Dao\TestpaperDao;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class CourseSetCoursesCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($source, $options)
    {
        $user = $this->biz['user'];
        $courseSet = $source;
        $newCourseSet = $options['newCourseSet'];

        $courses = $this->getCourseDao()->findCoursesByCourseSetIdAndStatus($courseSet['id'], null);

        $defaultCourseId = 0;
        $newCourses = array();

        foreach ($courses as $originCourse) {
            $newCourse = $this->partsFields($originCourse);
            $newCourse['courseSetId'] = $newCourseSet['id'];
            $newCourse['courseSetTitle'] = $newCourseSet['title'];
            $newCourse['creator'] = $user['id'];
            $newCourse['parentId'] = $originCourse['id'];
            $newCourse['price'] = $originCourse['originPrice'];
            $newCourse = $this->getCourseDao()->create($newCourse);

            $newCourses[] = $newCourse;

            if ($originCourse['id'] == $courseSet['defaultCourseId']) {
                $defaultCourseId = $newCourse['id'];
            }

            $options['newCourse'] = $newCourse;
            $options['originCourse'] = $originCourse;
            $this->doChildrenProcess($source, $options);
        }

        // 原课程defaultCourse被删除时，复制后defaultCourseId为课程下第一个计划的ID
        $defaultCourseId = empty($defaultCourseId) ? $newCourses[0]['id'] : $defaultCourseId;
        $this->getCourseSetDao()->update($newCourseSet['id'], array('defaultCourseId' => $defaultCourseId));

        $this->getCourseSetService()->updateCourseSetMinAndMaxPublishedCoursePrice($newCourseSet['id']);

        $this->updateQuestionsCourseId($newCourseSet['id']);
        $this->updateQuestionsLessonId($newCourseSet['id']);
        $this->updateExerciseRange($newCourseSet['id']);
        $this->resetCopyId($newCourseSet['id']);
    }

    protected function updateQuestionsCourseId($courseSetId)
    {
        $questions = $this->getQuestionDao()->findQuestionsByCourseSetId($courseSetId);
        $courseIds = ArrayToolkit::column($questions, 'courseId');

        $conditions = array(
            'parentIds' => $courseIds,
            'fromCourseSetId' => $courseSetId,
        );
        $parentCourses = $this->getCourseDao()->search($conditions, array(), 0, PHP_INT_MAX);
        $parentCourses = ArrayToolkit::index($parentCourses, 'parentId');

        foreach ($questions as $question) {
            if (empty($question['courseId'])) {
                continue;
            }

            $fields = array(
                'courseId' => empty($parentCourses[$question['courseId']]) ? 0 : $parentCourses[$question['courseId']]['id'],
            );

            $this->getQuestionDao()->update($question['id'], $fields);
        }
    }

    protected function updateQuestionsLessonId($courseSetId)
    {
        $questions = $this->getQuestionDao()->findQuestionsByCourseSetId($courseSetId);
        $taskIds = ArrayToolkit::column($questions, 'lessonId');

        $conditions = array(
            'copyIds' => $taskIds,
            'fromCourseSetId' => $courseSetId,
        );
        $parentTasks = $this->getTaskDao()->search($conditions, array(), 0, PHP_INT_MAX);
        $parentTasks = ArrayToolkit::index($parentTasks, 'copyId');

        foreach ($questions as $question) {
            if (empty($question['lessonId'])) {
                continue;
            }

            $fields = array(
                'lessonId' => empty($parentTasks[$question['lessonId']]) ? 0 : $parentTasks[$question['lessonId']]['id'],
            );

            $this->getQuestionDao()->update($question['id'], $fields);
        }
    }

    protected function updateExerciseRange($courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
            'type' => 'exercise',
        );

        $exercises = $this->getTestpaperDao()->search($conditions, array(), 0, PHP_INT_MAX);

        $taskIds = ArrayToolkit::column($exercises, 'lessonId');
        $conditions = array(
            'copyIds' => $taskIds,
            'fromCourseSetId' => $courseSetId,
        );
        $copyTasks = $this->getTaskDao()->search($conditions, array(), 0, PHP_INT_MAX);
        $copyTasks = ArrayToolkit::index($copyTasks, 'copyId');

        foreach ($exercises as $exercise) {
            if (empty($exercise['lessonId'])) {
                continue;
            }

            $metas = $exercise['metas'];
            $range = $metas['range'];
            $taskId = empty($range['lessonId']) ? 0 : $range['lessonId'];

            $range['lessonId'] = empty($copyTasks[$taskId]['id']) ? 0 : $copyTasks[$taskId]['id'];
            $metas['range'] = $range;

            $fields = array(
                'lessonId' => 0,
                'metas' => $metas,
            );

            $this->getTestpaperDao()->update($exercise['id'], $fields);
        }
    }

    /**
     * @param $newCourseSetId
     */
    protected function resetCopyId($newCourseSetId)
    {
        $connection = $this->biz['db'];
        $courses = $this->getCourseDao()->findCoursesByCourseSetIdAndStatus($newCourseSetId, null);
        if (!empty($courses)) {
            $courseIds = ArrayToolkit::column($courses, 'id');
            $courseIdsString = implode(',', $courseIds);
            $connection->exec("UPDATE `course_chapter` SET copyId = 0 WHERE courseId IN ({$courseIdsString})");
        }

        $connection->exec("UPDATE `course_v8` SET parentId = 0 WHERE courseSetId = {$newCourseSetId}");
        $connection->exec("UPDATE `course_task` SET copyId = 0 WHERE fromCourseSetId = {$newCourseSetId}");
        $connection->exec("UPDATE `activity` SET copyId = 0 where fromCourseSetId = {$newCourseSetId}");
        $connection->exec("UPDATE `testpaper_v8` SET copyId = 0 WHERE courseSetId = {$newCourseSetId}");
        $connection->exec("UPDATE `question` SET copyId = 0 WHERE courseSetId = {$newCourseSetId}");
    }

    protected function doChildrenProcess($source, $options)
    {
        $childrenNodes = $this->getChildrenNodes();
        foreach ($childrenNodes as $childrenNode) {
            $CopyClass = $childrenNode['class'];
            $copyClass = new $CopyClass($this->biz, $childrenNode, isset($childrenNode['auto']) ? $childrenNode['auto'] : true);
            $copyClass->copy($source, $options);
        }
    }

    protected function getFields()
    {
        return array(
            'title',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'summary',
            'goals',
            'audiences',
            'maxStudentNum',
            //'isFree',
            'price',
            // 'vipLevelId',
            'buyable',
            'tryLookable',
            'tryLookLength',
            'watchLimit',
            'services',
            'taskNum',
            'buyExpiryTime',
            'type',
            'approval',
            'income',
            'originPrice',
            'coinPrice',
            'originCoinPrice',
            'showStudentNumType',
            'serializeMode',
            'giveCredit',
            'about',
            'locationId',
            'address',
            'deadlineNotify',
            'daysOfNotifyBeforeDeadline',
            'useInClassroom',
            'singleBuy',
            'freeStartTime',
            'freeEndTime',
            'locked',
            'maxRate',
            'materialNum',
            'cover',
            'enableFinish',
            'compulsoryTaskNum',
            'rewardPoint',
            'taskRewardPoint',
            'courseType',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'expiryMode',
            'isDefault',
            'parentId',
            'locked',
            'status',
            'teacherIds',
            'lessonNum',
            'publishLessonNum',
            'subtitle',
        );
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('Question:QuestionDao');
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->biz->dao('Testpaper:TestpaperDao');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
