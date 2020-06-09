<?php

namespace Biz\Course\Copy;

use AppBundle\Common\ArrayToolkit;
use Biz\AbstractCopy;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Dao\TaskDao;
use Biz\Testpaper\Dao\TestpaperDao;

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
        $newCourses = [];

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
        $this->getCourseSetDao()->update($newCourseSet['id'], ['defaultCourseId' => $defaultCourseId]);

        $this->getCourseSetService()->updateCourseSetMinAndMaxPublishedCoursePrice($newCourseSet['id']);

        $this->resetCopyId($newCourseSet['id']);
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
        return [
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
        ];
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
