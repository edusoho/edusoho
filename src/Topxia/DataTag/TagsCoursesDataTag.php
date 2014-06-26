<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class TagsCoursesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取标签课程列表
     *
     * 可传入的参数：
     *   TagIds 必需 标签ID
     *   count    必需 课程数量，取值不超过10
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {	
  
        $tags = $this->getTagService()->findTagsByNames($arguments['tags']);

        $tagIds = array();

        foreach ($tags as $tagId) {
             array_push($tagIds, $tagId['id']);
        }

        if (empty($arguments['status'])) {
            $status = 'published';
        } else {
            $status = $arguments['status'];
        }

        $courses = $this->getCourseService()->findCoursesByTagIdsAndStatus($tagIds, $status, 0, $arguments['count']);

        return $this->getCourseTeachersAndCategories($courses);
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

}
