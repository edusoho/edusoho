<?php

namespace Biz\Course\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseDao;

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

        foreach ($courses as $originCourse) {
            $newCourse = $this->partsFields($originCourse);
            $newCourse['courseSetId'] = $newCourseSet['id'];
            $newCourse['creator'] = $user['id'];
            $newCourse = $this->getCourseDao()->create($newCourse);
            if ($newCourse['courseType'] == 'default') {
                $this->getCourseSetDao()->update($newCourseSet['id'], array('defaultCourseId' => $newCourse['id']));
            }

            $options['newCourse'] = $newCourse;
            $options['originCourse'] = $originCourse;
            $this->doChildrenProcess($source, $options);
        }
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

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }
}
