<?php

namespace AppBundle\Extensions\DataTag;

class ClassroomMemberDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取班级中某个学员的信息.
     *
     * 该DataTag返回了指定的班级中某学员的信息
     *
     * @param array $arguments 参数
     *                         classroomId:         班级id
     *                         userId:              用户id
     *
     * @return array 用户信息
     */
    public function getData(array $arguments)
    {
        return $this->getClassroomService()->getClassroomMember(
            $arguments['classroomId'], $arguments['userId']
        );
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
    }
}
