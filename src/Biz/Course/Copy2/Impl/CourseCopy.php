<?php

namespace Biz\Course\Copy2\Impl;

class CourseCopy extends AbstractEntityCopy
{
    public function copy($source, $config = array())
    {
        $user = $this->biz['user'];
        $courseSetId = $source['courseSetId'];
        if (!empty($config['newCourseSet'])) {
            $courseSetId = $config['newCourseSet']['id'];
        }

        $new = $this->copyFields($source);
        //通过教学计划复制出来的教学计划一定不是默认的。
        $new['isDefault'] = $courseSetId == $source['courseSetId'] ? 0 : $source['isDefault'];
        //标记是否是从默认教学计划转成非默认的，如果是则需要对chapter-task结构进行调整
        $modeChange = $new['isDefault'] != $source['isDefault'];
        $new['parentId'] = 0;
        $new['locked'] = 0;
        $new['courseSetId'] = $courseSetId;
        $new['creator'] = $user['id'];
        $new['status'] = 'draft';
        $new['teacherIds'] = array($user['id']);

        //course的自定义配置
        if (!empty($config['title'])) {
            $new['title'] = $config['title'];
        }
        if (!empty($config['learnMode'])) {
            //如果learnMode改变了，则任务列表需按照新的learnMode构建
            $new['learnMode'] = $config['learnMode'];
        }

        if (!empty($config['expiryMode'])) {
            $new['expiryMode'] = $config['expiryMode'];
            if ($config['expiryMode'] === 'days') {
                $new['expiryDays'] = $config['expiryDays'];
                $new['expiryStartDate'] = 0;
                $new['expiryEndDate'] = 0;
            } elseif ($config['expiryMode'] === 'end_date') {
                $new['expiryStartDate'] = 0;
                $new['expiryDays'] = 0;
                $new['expiryEndDate'] = $config['expiryEndDate'];
            } elseif ($config['expiryMode'] === 'date') {
                $new['expiryDays'] = 0;
                $new['expiryStartDate'] = $config['expiryStartDate'];
                $new['expiryEndDate'] = $config['expiryEndDate'];
            } else {//forever
                $new['expiryStartDate'] = 0;
                $new['expiryDays'] = 0;
                $new['expiryEndDate'] = 0;
            }
        }

        $new = $this->getCourseDao()->create($new);
        //@todo children copy
        // $this->childrenCopy($source, array('newCourse' => $new, 'modeChange' => $modeChange, 'isCopy' => false));

        return $new;
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
            'cover',
            'enableFinish',
            'publishedTaskNum',
        );
    }
}
