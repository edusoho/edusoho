<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class RecommendClassroomsDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取最新课程列表
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);
        
        $conditions = array(
            'status' => 'published'
        );
        
        $courses = $this->getCourseService()->searchCourses($conditions,'recommendedSeq', 0, $arguments['count']);

        $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime','desc'),
                0,
                $arguments['count']
        );

        $classroomIds = ArrayToolkit::column($classrooms,'id');

        $users = array();

        foreach ($classrooms as &$classroom) {
            if (empty($classroom['teacherIds'])) {
                $classroomTeacherIds=array();
            }else{
                $classroomTeacherIds=json_decode($classroom['teacherIds']);
            }

            $users[$classroom['id']] = $this->getUserService()->findUsersByIds($classroomTeacherIds);

        }

        $coursesOfClassroom = array();
        $coursesNum = array();

        foreach ($classroomIds as $key => $value) {
            $classroomCourses=$this->getClassroomService()->findCoursesByClassroomId($value);
            $courseIds=ArrayToolkit::column($classroomCourses,'courseId');

            $courses=$this->getCourseService()->findCoursesByIds($courseIds);
            $coursesOfClassroom[$value] = $courses;
            $coursesNum[$value] = count($coursesOfClassroom[$value]);
        }

        return array('classrooms'=>$classrooms,
            'users'=>$users,
            'coursesNum'=>$coursesNum
            );
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
