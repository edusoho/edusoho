<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class GetClassroomByCourseIdDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取推荐班级列表
     *
     * 可传入的参数：
     *   courseId    必需 课程ID
     * 
     * @param  array $arguments 参数
     * @return array 获取包含该课程的班级
     */
    public function getData(array $arguments)
    {	

        $classroom = $this->getClassroomService()->findClassroomByCourseId($arguments['courseId']);
        if ($classroom) {
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);
        }
        
        return $classroom;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}
