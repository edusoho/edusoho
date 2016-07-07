<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class RecommendCoursesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取推荐课程列表
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   categoryCode 可选　分类CODE
     *   type 可选　课程类型：live直播, normal 普通
     *   count    必需 课程数量，取值不能超过100
     *   notFill     可选 推荐课程不足时不填充课程数，默认:false
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);

        $conditions = array('status' => 'published', 'recommended' => 1 ,'parentId' => 0);

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        if (!empty($arguments['categoryCode'])) {
            $category = $this->getCategoryService()->getCategoryByCode($arguments['categoryCode']);
            $conditions['categoryId'] = empty($category) ? -1 : $category['id'];
        }

        if (!empty($arguments['type'])) {
            $conditions['type'] = $arguments['type'];
        }
        
        $courses = $this->getCourseService()->searchCourses($conditions,'recommendedSeq', 0, $arguments['count']);
        $fillCoursesCount = $arguments['count'] - count($courses);
        if ($fillCoursesCount > 0 && empty($arguments['notFill'])) {
            $conditions['recommended'] = 0;
            $coursesTemp = $this->getCourseService()->searchCourses($conditions,'createdTime', 0, $fillCoursesCount);
            $courses = array_merge($courses, $coursesTemp);
        }
        
        return $this->getCourseTeachersAndCategories($courses);
    }


    protected function autoFillCourses()
    {



    }
}
