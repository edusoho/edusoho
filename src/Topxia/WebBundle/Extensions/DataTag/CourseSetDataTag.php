<?php

namespace Topxia\WebBundle\Extensions\DataTag;


class CourseSetDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程
     *
     * 可传入的参数：
     *   id 必需 课程ID
     * 
     * @param  array $arguments 参数
     * @return array 课程
     */
    
    public function getData(array $arguments)
    {
        if (empty($arguments['id'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('id参数缺失'));
        }

    	$set = $this->getCourseSetService()->getCourseSet($arguments['id']);

        return $set;
    }
}

