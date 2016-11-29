<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
// use Topxia\Service\Article\ArticleService;

class CourseRelatedArticlesDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     *  根据课程id取相同标签的已经发布的资讯
     *  @return  array 资讯
     */

    public function getData(array $arguments)
    {
        $this->checkCourseId( $arguments);
        $course = $this->getCourseService()->getCourse($arguments['courseId']);

        if(empty($course))
        {
            return array();
        }

        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $arguments['courseId']));

        $tagIds = ArrayToolkit::column($tags, 'id');

        $count=$arguments['count'];

        if(empty($count)){
            $count=5;
        }

        $articles = $this->getArticleService()->findPublishedArticlesByTagIdsAndCount($tagIds,$count);
        return $articles;
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}
