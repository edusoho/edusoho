<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseDao;

/**
 * 同步链说明：
 * Course 教学计划信息
 * - Task 任务列表.
 */
class CourseProductSync extends AbstractEntitySync
{
    /**
     * @param mixed $source
     * @param array $config array(syncCourseId)
     *
     * @return array|mixed
     */
    protected function syncEntity($source, $config = [])
    {
        $course = array_merge($source, $config);

        $syncCourseFields = $this->filterFields($course);
        $syncCourseFields['price'] = $course['suggestionPrice'];
        $syncCourseFields['originPrice'] = $course['suggestionPrice'];
        $syncCourse = $this->getCourseDao()->update($course['syncCourseId'], $syncCourseFields);

        $config = ['newCourse' => $syncCourse, 'isCopy' => false];
        $this->processChainsDoSync($source, $config);

        return $syncCourse;
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        $course = array_merge($source, $config);

        $syncCourseFields = $this->filterUpdateFields($course);
        $syncCourse = $this->getCourseDao()->update($course['syncCourseId'], $syncCourseFields);

        $config = ['newCourse' => $syncCourse, 'isCopy' => false];
        $this->processChainsDoUpdate($source, $config);
    }

    protected function getFields()
    {
        return [
            'title', 'subtitle', 'summary', 'type', 'cover', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate',
            'goals', 'audiences', 'maxStudentNum', 'isFree', 'price', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services',
            'taskNum', 'originPrice', 'coinPrice', 'originCoinPrice', 'showStudentNumType', 'serializeMode', 'about', 'deadlineNotify',
            'daysOfNotifyBeforeDeadline', 'freeStartTime', 'freeEndTime', 'cover', 'buyExpiryTime', 'enableFinish', 'materialNum', 'compulsoryTaskNum',
            'lessonNum', 'publishLessonNum', 'showServices', 'courseType', 'enableAudio', 'isHideUnpublish',
        ];
    }

    protected function filterUpdateFields($course)
    {
        $fields = [
            'learnMode', 'tryLookable', 'tryLookLength', 'watchLimit',
            'taskNum', 'enableFinish', 'materialNum', 'compulsoryTaskNum',
            'lessonNum', 'publishLessonNum', 'enableAudio',
        ];

        return ArrayToolkit::parts($course, $fields);
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
