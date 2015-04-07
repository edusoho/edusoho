<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class MemberRecentlyLearnedDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取个人正在学习课程
     *
     *   count    必需 
     *   user     必须
     * @param  array $arguments 参数
     * @return array 个人动态
     */
    public function getData(array $arguments)
    {   
        $user = $arguments['user'];

        $conditions = array(
            'userId' => $user->id,
            'objectType' => array('course', 'lesson')
        );

        $status = $this->getStatusService()->searchStatuses($conditions,array('createdTime','DESC'),0,1);

        $course = array();
        $nextLearnLesson = array();
        $progress = array();
        $teachers = array();

        if ($status) {
            $course = $this->getCourseService()->getCourse($status[0]['properties']['course']['id']);

            if ($course && $course['status'] == 'published'){
                $member = $this->getCourseService()->getCourseMember($course['id'], $user->id);
                $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);

                $course['nextLearnLesson'] = $this->getCourseService()->getUserNextLearnLesson($user->id, $course['id']);

                $course['progress']= $this->calculateUserLearnProgress($course, $member);
            } else {
                $course = array();
            }
            
        }

        return $course;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    private function getStatusService() 
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum']
        );
    }

}
