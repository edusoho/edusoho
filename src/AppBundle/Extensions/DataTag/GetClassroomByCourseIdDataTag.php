<?php

namespace AppBundle\Extensions\DataTag;

class GetClassroomByCourseIdDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取推荐班级列表.
     *
     * 可传入的参数：
     *   courseId    必需 课程ID
     *
     * @param array $arguments 参数
     *
     * @return array 获取包含该课程的班级
     */
    public function getData(array $arguments)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($arguments['courseId']);
        if ($classroom) {
            $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        }

        return $classroom;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
    }
}
