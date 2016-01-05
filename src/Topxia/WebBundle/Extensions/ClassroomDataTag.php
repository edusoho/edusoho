<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class ClassroomDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个班级
     *
     * 可传入的参数：
     *   classroomId 必需 班级ID
     *
     * @param  array $arguments 参数
     * @return array 班级
     */
    public function getData(array $arguments)
    {
        $this->checkClassroomId($arguments);
        return $this->getClassroomService()->getClassroom($arguments['classroomId']);
    }

    protected function checkClassroomId(array $arguments)
    {
        if (empty($arguments['classroomId'])) {
            throw new \InvalidArgumentException("classroomId参数缺失");
        }
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
