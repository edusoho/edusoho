<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class CourseRelatedArticlesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     *  根据课程id取相同标签的已经发布的资讯.
     *
     *  @return  array 资讯
     */
    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);
        $course = $this->getCourseService()->getCourse($arguments['courseId']);

        if (empty($course)) {
            return array();
        }

        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $arguments['courseId']));

        $tagIds = ArrayToolkit::column($tags, 'id');

        $count = 5;
        if (!empty($arguments['count'])) {
            $count = $arguments['count'];
        }

        return $this->getArticleService()->findPublishedArticlesByTagIdsAndCount($tagIds, $count);
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->getBiz()->service('Article:ArticleService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->getBiz()->service('Taxonomy:TagService');
    }
}
