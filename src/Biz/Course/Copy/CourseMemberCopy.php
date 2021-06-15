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

        $courseMembers = array_merge($this->getMemberDao()->findByCourseIdAndRole($course['id'], 'teacher'),
            $this->getMemberDao()->findByCourseIdAndRole($course['id'], 'assistant')
        );

        if (!empty($courseMembers)) {
            $newMembers = [];
            $teacherIds = [];

            foreach ($courseMembers as $member) {
                $member = $this->partsFields($member);
                $member['courseId'] = $newCourse['id'];
                $member['courseSetId'] = $newCourse['courseSetId'];
                $member['multiClassId'] = !empty($member['multiClassId']) && !empty($options['newMultiClass']) ? $options['newMultiClass']['id'] : 0;

                if ($member['isVisible'] && 'teacher' == $member['role']) {
                    $teacherIds[] = $member['userId'];
                }

                $newMembers[] = $member;
            }

            if (!empty($newMembers)) {
                $this->getMemberDao()->batchCreate($newMembers);
            }

            if (!empty($teacherIds)) {
                $this->getCourseDao()->update($newCourse['id'], ['teacherIds' => $teacherIds]);
            }
        }
    }

    public function afterCopy($source, $options)
    {
        return;
    }

    protected function getFields()
    {
        return [
            'userId',
            'seq',
            'isVisible',
            'remark',
            'deadline',
            'deadlineNotified',
            'role',
            'multiClassId',
        ];
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
