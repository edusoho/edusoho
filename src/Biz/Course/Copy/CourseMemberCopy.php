<?php

namespace Biz\Course\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;

class CourseMemberCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        return;
    }

    public function doCopy($source, $options)
    {
        $course = $options['originCourse'];
        $newCourse = $options['newCourse'];

        $courseMembers = $this->getMemberDao()->findByCourseIdAndRole($course['id'], 'teacher');

        if (!empty($courseMembers)) {
            $newMembers = array();
            $teacherIds = array();

            foreach ($courseMembers as $member) {
                $member = $this->partsFields($member);
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

            if (!empty($teacherIds)) {
                $this->getCourseDao()->update($newCourse['id'], array('teacherIds' => $teacherIds));
            }
        }
    }

    public function afterCopy($source, $options)
    {
        return;
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

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
