<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;

class CoursesClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->doCloneCourseSetCourses($source, $options);
    }

    private function doCloneCourseSetCourses($courseSet, $options)
    {
        $user = $this->biz['user'];
        $newCourseSet = $options['newCourseSet'];
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
        foreach ($courses as $originCourse) {
            $newCourse = $this->filterFields($originCourse);
            $newCourse['courseSetId'] = $newCourseSet['id'];
            $newCourse['creator'] = $user['id'];
            $newCourse = $this->getCourseDao()->create($newCourse);
            $cloneCourse = new CourseClone($this->biz);
            $options['newCourse'] = $newCourse;
            $cloneCourse->clones($originCourse, $options);
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
            'isFree',
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
