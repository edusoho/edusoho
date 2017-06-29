<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;

class CourseMemberCopy extends AbstractEntityCopy
{
    public function __construct($biz, $node)
    {
        parent::__construct($biz, $node);
    }

    /**
     * @param mixed $source oldCourse
     * @param array $config $config['newCourse'] = newCourse
     */
    protected function copyEntity($source, $config = array())
    {
        $newCourse = $config['newCourse'];

        return $this->doCopyCourseMember($source, $newCourse);
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

    protected function doCopyCourseMember($oldCourse, $newCourse)
    {
        $members = $this->getMemberDao()->findByCourseIdAndRole($oldCourse['id'], 'teacher');
        if (!empty($members)) {
            $teacherIds = array();
            foreach ($members as $member) {
                $member = $this->copyFields($member);
                $member['courseId'] = $newCourse['id'];
                $member['courseSetId'] = $newCourse['courseSetId'];
                $member['role'] = 'teacher';

                if ($member['isVisible']) {
                    $teacherIds[] = $member['userId'];
                }
                $this->getMemberDao()->create($member);
            }
            if (!empty($teacherIds)) {
                $this->getCourseDao()->update($newCourse['id'], array('teacherIds' => $teacherIds));
            }
        }
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
