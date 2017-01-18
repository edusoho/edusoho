<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Copy\AbstractEntityCopy;

class CourseCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * Course 教学计划信息
     * - Testpaper （教学计划下创建的Testpaper，实际被Activity引用）
     * - Task 任务列表
     * @param $biz
     */
    public function __construct($biz)
    {
        $this->biz  = $biz;
        $children   = array();
        $children[] = new TaskCopy($biz);
        // $children[] = new CourseTestpaperCopy($biz);
    }

    /*
     * $source = $originalCourse
     * $config : $newCourseSet
     */
    protected function _copy($source, $config = array())
    {
        $this->addError('CourseCopy', 'copy source:'.json_encode($source));

        $user        = $this->biz['user'];
        $courseSetId = $source['courseSetId'];
        if (!empty($config['newCourseSet'])) {
            $courseSetId = $config['newCourseSet']['id'];
        }

        $new = $this->doCopy($source);
        //通过教学计划复制出来的教学计划一定不是默认的。
        $new['isDefault']   = $courseSetId == $source['courseSetId'] ? 0 : $source['isDefault'];
        $new['parentId']    = $source['id'];
        $new['courseSetId'] = $courseSetId;
        $new['creator']     = $user['id'];
        $new['status']      = 'published';
        $new['teacherIds']  = array($user['id']);

        $new = $this->getCourseDao()->create($new);
        $this->doCopyCourseMember($new);
        $this->addError('CourseCopy', 'copy children:'.json_encode($this->children));
        $this->childrenCopy($source, array('newCourse' => $new));

        return $new;
    }

    private function doCopy($source)
    {
        $fields = array(
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
            'vipLevelId',
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
            'categoryId',
            'about',
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'locationId',
            'address',
            'discountId',
            'discount',
            'deadlineNotify',
            'daysOfNotifyBeforeDeadline',
            'useInClassroom',
            'singleBuy',
            'freeStartTime',
            'freeEndTime',
            'locked',
            'maxRate',
            'orgId',
            'orgCode',
            'cover',
            'enableFinish'
        );

        $new = array();
        foreach ($fields as $field) {
            $new[$field] = $source[$field];
        }

        return $new;
    }

    private function doCopyCourseMember($course)
    {
        $member = array(
            'courseId'    => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'userId'      => $this->biz['user']['id'],
            'role'        => 'teacher',
            'seq'         => 0,
            'isVisible'   => 1
        );
        $this->getMemberDao()->create($member);
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->biz->dao('Course:CourseMemberDao');
    }
}
