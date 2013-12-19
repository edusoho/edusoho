<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class PopularCoursesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取最热门课程列表
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
        $this->checkCount($arguments);
        if (empty($arguments['categoryId'])){
            $conditions = array('status' => 'published');
        } else {
            $conditions = array('status' => 'published', 'categoryId' => $arguments['categoryId']);
        }
        $courses = $this->getCourseService()->searchCourses($conditions,'popular', 0, $arguments['count']);

        return $this->getCourseTeachersAndCategories($courses);
    }

}
