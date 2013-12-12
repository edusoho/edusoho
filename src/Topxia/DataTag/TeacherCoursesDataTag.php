<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class TeacherCoursesDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取特定老师的课程列表
     *
     * 可传入的参数：
     *   userId   必需 老师ID
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
     
        $conditions = array('status' => 'published', 'userId' => $arguments['userId']);

    	return $this->getCoursService()->searchCourses($conditions,'latest', 0, $arguments['count']);
    }

    protected function getCoursService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}