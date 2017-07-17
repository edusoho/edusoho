<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Dao\CourseMemberDao;

class CourseMemberClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseMembers($source, $options);
    }

    private function cloneCourseMembers($source, $options)
    {
        $newCourse = $options['newCourse'];
        $courseMembers = $this->getMemberDao()->findByCourseIdAndRole($source['id'], 'teacher');

        if (!empty($courseMembers)) {
            $newMembers = array();

            foreach ($courseMembers as $member) {
                $member = $this->filterFields($member);
                $member['courseId'] = $newCourse['id'];
                $member['courseSetId'] = $newCourse['courseSetId'];
                $member['role'] = 'teacher';

                if ($member['isVisible']) {
                    $teacherIds[] = $member['userId'];
                }

                $newMembers[] = $member;
            }

            if (!empty($newMembers)) {
                $this->getMemberDao()->batchCreate($newMembers);
            }
        }
    }

    protected function getFields()
    {
        return array(
            'userId',
            'seq',
            'isVisible',
            'remark',
            'deadline',
            'deadlineNotified',
        );
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->biz->dao('Course:CourseMemberDao');
    }
}
