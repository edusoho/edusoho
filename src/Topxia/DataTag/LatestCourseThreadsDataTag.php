<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class LatestCourseThreadsDataTag extends CourseBaseDataTag implements DataTag  
{
    
    /**
     * 获取最新发表的课程话题列表
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   count 必需 课程话题数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程话题
     */

    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);
        $this->checkCount($arguments);

        $conditions = array( 'courseId' => $arguments['courseId']);
    	$threads = $this->getThreadService()->searchThreads($conditions, 'created', 0, $arguments['count']);
        $threads['course'] = $this->getCourseService()->getCourse($arguments['courseId']);

        return $threads;
    }


}
