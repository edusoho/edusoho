<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class TagsCoursesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取标签课程列表.
     *
     * 可传入的参数：
     *   tags 必需 标签名称like array('aa')
     *   count    必需 课程数量，取值不超过10
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $tags = $this->getTagService()->findTagsByNames($arguments['tags']);

        if (empty($tags)) {
            return array();
        }
        $tagIds = ArrayToolkit::column($tags, 'id');
        $tagOwners = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, 'courseSet');

        if (empty($tagOwners)) {
            return array();
        }

        $tagOwners = ArrayToolkit::group($tagOwners, 'ownerId');
        $courseSetIds = array();
        $tagCount = count($tags);

        $filter = array_filter($tagOwners, function ($tags) {
            if (count($tags) >= 2) {
                return 1;
            }
        });

        if (empty($filter)) {
            return array();
        }

        $courseSetIds = array_keys($filter);

        $condition = array(
            'ids' => $courseSetIds,
        );

        return $this->getCourseSetService()->searchCourseSets($condition, array('createdTime' => 'DESC'), 0, $arguments['count']);
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
