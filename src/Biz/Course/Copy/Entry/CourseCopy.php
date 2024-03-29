<?php

namespace Biz\Course\Copy\Entry;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseDao;
use Biz\Goods\Mediator\CourseSpecsMediator;

/**
 * 复制链说明：
 * Course 教学计划信息
 * - Testpaper （教学计划下创建的Testpaper，实际被Activity引用）
 * - Task 任务列表.
 */
class CourseCopy extends AbstractEntityCopy
{
    /**
     * @param mixed $source
     * @param array $course
     *
     * @return array|mixed
     */
    protected function copyEntity($source, $course = [])
    {
        $course = array_merge($source, $course);

        $newCourse = $this->processCourse($course);
        //标记是否是从默认教学计划转成非默认的，如果是则需要对chapter-task结构进行调整
        $modeChange = $newCourse['courseType'] != $source['courseType'];
        $newCourse = $this->parseExpiryMode($course, $newCourse);

        $newCourse = $this->getCourseDao()->create($newCourse);

        $course = ['newCourse' => $newCourse, 'modeChange' => $modeChange, 'isCopy' => false];
        $this->getCourseSpecsMediator()->onCreate($newCourse);
        $this->getCourseSpecsMediator()->onUpdateNormalData($newCourse);
        if ('published' === $newCourse['status']) {
            $this->getCourseSpecsMediator()->onPublish($newCourse);
        }
        $this->processChainsDoCopy($source, $course);

        return $newCourse;
    }

    protected function getFields()
    {
        return [
            'title',
            'courseSetTitle',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'summary',
            'goals',
            'audiences',
            'maxStudentNum',
            'isFree',
            'price',
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
            'taskNumber',
            'compulsoryTaskNum',
            'enableAudio',
            'lessonNum',
            'publishLessonNum',
            'subtitle',
            'taskDisplay',
        ];
    }

    protected function processCourse($course)
    {
        $user = $this->biz['user'];
        $newCourse = $this->filterFields($course);

        $courseSetId = $course['courseSetId'];
        if (!empty($course['newCourseSet'])) {
            $courseSetId = $course['newCourseSet']['id'];
        }

        //通过教学计划复制出来的教学计划一定不是默认的。
        $newCourse['isDefault'] = 0;
        $newCourse['parentId'] = 0;
        $newCourse['locked'] = 0;
        $newCourse['courseSetId'] = $courseSetId;
        $newCourse['creator'] = $user['id'];
        $newCourse['status'] = 'draft';
        $newCourse['teacherIds'] = [$user['id']];

        return $newCourse;
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @param $course
     * @param $newCourse
     *
     * @return mixed
     */
    protected function parseExpiryMode($course, $newCourse)
    {
        if (!empty($course['expiryMode'])) {
            $newCourse['expiryMode'] = $course['expiryMode'];
            if ('days' === $course['expiryMode']) {
                $newCourse['expiryDays'] = $course['expiryDays'];
                $newCourse['expiryStartDate'] = 0;
                $newCourse['expiryEndDate'] = 0;
            } elseif ('end_date' === $course['expiryMode']) {
                $newCourse['expiryStartDate'] = 0;
                $newCourse['expiryDays'] = 0;
                $newCourse['expiryEndDate'] = $course['expiryEndDate'];
            } elseif ('date' === $course['expiryMode']) {
                $newCourse['expiryDays'] = 0;
                $newCourse['expiryStartDate'] = $course['expiryStartDate'];
                $newCourse['expiryEndDate'] = $course['expiryEndDate'];
            } else {//forever
                $newCourse['expiryStartDate'] = 0;
                $newCourse['expiryDays'] = 0;
                $newCourse['expiryEndDate'] = 0;
            }
        }

        return $newCourse;
    }

    /**
     * @return CourseSpecsMediator
     */
    protected function getCourseSpecsMediator()
    {
        return $this->biz['specs.mediator.course'];
    }
}
