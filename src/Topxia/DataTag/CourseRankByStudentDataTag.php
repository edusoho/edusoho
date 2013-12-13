<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseRankByStudentDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取按学生数排行的课程
     *
     * 可传入的参数：
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
     
        $conditions = array('status' => 'published');

    	return $this->getCourseService()->searchCourses($conditions,'studentNum', 0, $arguments['count']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}


?>