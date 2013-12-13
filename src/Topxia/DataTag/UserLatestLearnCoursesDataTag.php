<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class UserLatestLearnCoursesDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取用户当前学习的课程
     *
     * 可传入的参数：
     *   userId   必需 用户ID
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */

    public function getData(array $arguments)
    {	
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException("count参数缺失");
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException("count参数超出最大取值范围");
        }
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException("userId参数缺失");
        }
    	return $this->getCourseService()->findUserLeaningCourses($arguments['userId'], 0, $arguments['count']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}


?>