<?php

namespace Biz\Course\Copy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Classroom\Dao\ClassroomMemberDao;

class ClassroomTeacherCopy extends AbstractEntityCopy
{
    public function __construct($biz, $node)
    {
        parent::__construct($biz, $node);
    }

    /**
     * @param mixed $source oldCourse
     * @param array $config $config['classroomId'] = newClassroomId
     */
    protected function copyEntity($source, $config = array())
    {
        $classroomId = $config['classroomId'];

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
        if (empty($existTeachers)) {
            $existTeachers = array();
        } else {
            $existTeachers = ArrayToolkit::index($existTeachers, 'userId');
        }

        $teachers = $this->getMemberDao()->findByCourseIdAndRole($oldCourse['id'], 'teacher');
        if (!empty($teachers)) {
            foreach ($teachers as $teacher) {
                if (!empty($existTeachers[$teacher['userId']])) {
                    continue;
                }
                $this->getClassroomMemberDao()->create(array(
                    'classroomId' => $classroomId,
                    'userId' => $teacher['userId'],
                    'role' => array('teacher'),
                ));
            }
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
