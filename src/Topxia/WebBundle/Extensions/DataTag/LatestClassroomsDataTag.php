<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class LatestClassroomsDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取推荐班级列表
     *
     * 可传入的参数：
     *   count    必需 班级数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 班级推荐列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);
        
        $conditions = array(
            'status' => 'published',
            'showable' => 1
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime','DESC'),
                0,
                $arguments['count']
        );

        $users = array();

        foreach ($classrooms as &$classroom) {
            if (empty($classroom['teacherIds'])) {
                $classroomTeacherIds=array();
            }else{
                $classroomTeacherIds=$classroom['teacherIds'];
            }

            $users = $this->getUserService()->findUsersByIds($classroomTeacherIds);
            $classroom['users'] = $users;
        }
        
        return $classrooms;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}
