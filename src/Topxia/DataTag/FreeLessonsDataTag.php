<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class FreeLessonsDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取免费课程课程列表
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
            $arguments['count'] = 4;
        }   

        return $this->getCourseService()->searchLessons(array('free' => 1), array('createdTime', 'DESC'), 0, $arguments['count']);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}
