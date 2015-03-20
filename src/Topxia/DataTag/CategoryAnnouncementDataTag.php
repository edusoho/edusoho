<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CategoryAnnouncementDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取公告列表
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   count    必需 课程数量，取值不超过10
     * 
     * @param  array $arguments 参数
     * @return array 公告列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);

        $conditions = array();
        $conditions['status'] = 'published';

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        } 

        $courseCount =  $this->getCourseService()->searchCourseCount($conditions);
        
        $courses = $this->getCourseService()->searchCourses($conditions,'latest', 0, $courseCount);

        $ids = array();

        foreach ($courses as $course) {
            array_push($ids, $course['id']);
        }

        $announcement = $this->getCourseService()->findAnnouncementsByCourseIds($ids, 0, $arguments['count']);

        return $announcement;
    }
}
