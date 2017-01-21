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
        parent::__construct($biz, 'course');
    }

    /*
     * $source = $originalCourse
     * $config : $newCourseSet
     */
    protected function _copy($source, $config = array())
    {
        $user        = $this->biz['user'];
        $courseSetId = $source['courseSetId'];
        if (!empty($config['newCourseSet'])) {
            $courseSetId = $config['newCourseSet']['id'];
        }

        $new = $this->doCopy($source);
        //通过教学计划复制出来的教学计划一定不是默认的。
        $new['isDefault'] = $courseSetId == $source['courseSetId'] ? 0 : $source['isDefault'];
        //标记是否是从默认教学计划转成非默认的，如果是则需要对chapter-task结构进行调整
        $modeChange         = $new['isDefault'] != $source['isDefault'];
        $new['parentId']    = $source['id'];
        $new['locked'] = 0;
        $new['courseSetId'] = $courseSetId;
        $new['creator']     = $user['id'];
        $new['status']      = 'published';
        $new['teacherIds']  = array($user['id']);

        //course的自定义配置
        if (!empty($config['title'])) {
            $new['title'] = $config['title'];
        }
        if (!empty($config['learnMode'])) {
            //todo 如果learnMode改变了，则任务列表需按照新的learnMode构建
            $new['learnMode'] = $config['learnMode'];
        }
        if (!empty($config['expiryMode'])) {
            $new['expiryMode'] = $config['expiryMode'];
            if ($config['expiryMode'] == 'days') {
                $new['expiryDays'] = $config['expiryDays'];
            } else {
                $new['expiryStartDate'] = $config['expiryStartDate'];
                $new['expiryEndDate']   = $config['expiryEndDate'];
            }
        }

        $new = $this->getCourseDao()->create($new);
        $this->doCopyCourseMember($new);
        $this->childrenCopy($source, array('newCourse' => $new, 'modeChange' => $modeChange, 'isCopy' => false));

        return $new;
    }

    protected function doCopy($source)
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
            'cover',
            'enableFinish'
        );

        $new = array();
        foreach ($fields as $field) {
            if (!empty($source[$field]) || $source[$field] == 0) {
                $new[$field] = $source[$field];
            }
        }

        return $new;
    }

    protected function doCopyCourseMember($course)
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
