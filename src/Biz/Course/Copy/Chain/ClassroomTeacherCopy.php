<?php

namespace Biz\Course\Copy\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Classroom\Dao\ClassroomMemberDao;

class ClassroomTeacherCopy extends AbstractEntityCopy
{
    protected function getFields()
    {
        // TODO: Implement getFields() method.
    }

    /**
     * @param mixed $source oldCourse
     * @param array $course $config['classroomId'] = newClassroomId
     */
    protected function copyEntity($source, $course = array())
    {
        $classroomId = $course['classroomId'];

        return $this->doCopyTeachersToClassroom($source, $classroomId);
    }

    protected function doCopyTeachersToClassroom($oldCourse, $classroomId)
    {
        $existTeachers = $this->getClassroomMemberDao()->findByClassroomIdAndRole(
            $classroomId,
            'teacher',
            0,
            PHP_INT_MAX
        );

        $existTeachers = ArrayToolkit::index($existTeachers, 'userId');

        $teachers = $this->getMemberDao()->findByCourseIdAndRole($oldCourse['id'], 'teacher');

        if (empty($teachers)) {
            return;
        }

        $newTeachers = array();
        foreach ($teachers as $teacher) {
            if (!empty($existTeachers[$teacher['userId']])) {
                continue;
            }

            $newTeacher = array(
                'classroomId' => $classroomId,
                'userId' => $teacher['userId'],
                'role' => array('teacher'),
            );

            $newTeachers[] = $newTeacher;
        }

        if (!empty($newTeachers)) {
            $this->getClassroomMemberDao()->batchCreate($newTeachers);
        }
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->biz->dao('Course:CourseMemberDao');
    }

    /**
     * @return ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->biz->dao('Classroom:ClassroomMemberDao');
    }
}
