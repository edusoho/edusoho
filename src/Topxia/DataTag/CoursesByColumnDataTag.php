<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CoursesByColumnDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取最新课程列表
     *
     * 可传入的参数：
     *   columnId 可选 专栏id
     *   columnCode 可选 专栏code
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {   
        $this->checkCount($arguments);

        $conditions = array();
        $conditions['status'] = 'published';

        if (!empty($arguments['columnId'])) {
            $conditions['columnId'] = $arguments['columnId'];
        } elseif (!empty($arguments['columnCode'])) {
            $column = $this->getColumnService()->getColumnByCode($arguments['columnCode']);
           
            $conditions['columnId'] = empty($column) ? -1 : $column['id'];
        }

        $courses = $this->getCourseSearchService()->searchCourses($conditions,'latest', 0, $arguments['count']);

        return $this->getCourseTeachersAndCategories($courses);
    }

    protected function getColumnService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
    }

    protected function getCourseSearchService(){
        return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
    }

    
}
