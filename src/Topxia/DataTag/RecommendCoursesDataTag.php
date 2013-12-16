<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class RecommendCoursesDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取推荐课程列表
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
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
        if (empty($arguments['categoryId'])){
            $conditions = array('status' => 'published', 'recommended' => 1 );
        } else {
            $conditions = array('status' => 'published', 'recommended' => 1 ,'categoryId' => $arguments['categoryId']);
        }
    	return $this->getCourseService()->searchCourses($conditions,'recommended', 0, $arguments['count']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}


?>