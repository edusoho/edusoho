<?php

namespace AppBundle\Extensions\DataTag;

/**
 * @todo  rename LatestClassroomsDataTag
 */
class ClassroomsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新课程列表.
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 班级列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = array(
            'status' => 'published',
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('createdTime' => 'desc'),
            0,
            $arguments['count']
        );

        foreach ($classrooms as $key => $classroom) {
            if (empty($classroom['teacherIds'])) {
                $classroomTeacherIds = array();
            } else {
                $classroomTeacherIds = $classroom['teacherIds'];
            }

            $classrooms[$key]['teachers'] = $this->getUserService()->findUsersByIds($classroomTeacherIds);
        }

        return $classrooms;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
    }
}
