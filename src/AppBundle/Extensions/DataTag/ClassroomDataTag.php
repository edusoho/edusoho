<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Classroom\Service\ClassroomService;

class ClassroomDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个班级.
     *
     * 可传入的参数：
     *   classroomId 班级ID
     *   courseId 教学计划ID
     *
     * @param array $arguments 参数
     *
     * @return array 班级
     */
    public function getData(array $arguments)
    {
        if (!empty($arguments['classroomId'])) {
            return $this->getClassroomService()->getClassroom($arguments['classroomId']);
        } elseif (!empty($arguments['courseId'])) {
            return $this->getClassroomService()->getClassroomByCourseId($arguments['courseId']);
        } else {
            throw new \InvalidArgumentException('classroomId or courseId required');
        }
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
