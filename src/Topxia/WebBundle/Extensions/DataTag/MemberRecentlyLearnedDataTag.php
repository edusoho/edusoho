<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

/**
 * @todo
 */
class MemberRecentlyLearnedDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取个人正在学习课程
     * 
     *   user     必须
     * @param  array $arguments 参数
     * @return array 个人正在学习课程相关信息
     */
    public function getData(array $arguments)
    {   
        $user = $arguments['user'];

        $conditions = array(
            'userId' => $user->id,
        );

        $lesson = $this->getCourseService()->searchLearns($conditions,array('startTime','DESC'),0,1);

        $course = array();
        $nextLearnLesson = array();
        $progress = array();
        $teachers = array();

        if ($lesson) {
            $course = $this->getCourseService()->getCourse($lesson[0]['courseId']);

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
