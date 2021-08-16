<?php

namespace Biz\Course\Copy\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseMemberDao;

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
    protected function copyEntity($source, $course = [])
    {
        $classroomId = $course['classroomId'];

        return $this->doCopyTeachersToClassroom($source, $classroomId);
    }

    protected function doCopyTeachersToClassroom($oldCourse, $classroomId)
    {
        $teachers = $this->getMemberDao()->findByCourseIdAndRole($oldCourse['id'], 'teacher');

        if (empty($teachers)) {
            return;
        }

        $teacherIds = ArrayToolkit::column($teachers, 'userId');
        $existMembers = $this->getClassroomMemberDao()->findByClassroomIdAndUserIds($classroomId, $teacherIds);
        $existMembers = ArrayToolkit::index($existMembers, 'userId');

        $newTeachers = $needUpdateTeachers = [];
        foreach ($teachers as $teacher) {
            if (!empty($existMembers[$teacher['userId']])) {
                $existMember = $existMembers[$teacher['userId']];
                if (in_array('teacher', $existMember['role'])) {
                    continue;
                }
                if (in_array('student', $existMember['role'])) {
                    $needUpdateTeacher = [
                        'id' => $existMember['id'],
                        'role' => array_merge($existMember['role'], ['teacher']),
                    ];
                    $needUpdateTeachers[] = $needUpdateTeacher;
                    continue;
                }
            }

            $newTeacher = [
                'classroomId' => $classroomId,
                'userId' => $teacher['userId'],
                'role' => ['teacher'],
            ];

            $newTeachers[] = $newTeacher;
        }

        if (!empty($needUpdateTeachers)) {
            $this->getClassroomMemberDao()->batchUpdate(array_column($needUpdateTeachers, 'id'), $needUpdateTeachers);
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
