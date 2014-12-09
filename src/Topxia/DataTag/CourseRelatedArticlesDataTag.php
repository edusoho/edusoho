<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
// use Topxia\Service\Article\ArticleService;

class CourseRelatedArticlesDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     *       根据课程id取相同标签的已经发布的资讯
     *	@return  array 资讯
     */

    public function getData(array $arguments)
    {
       	$this->checkCourseId( $arguments);
	$course = $this->getCourseService()->getCourse($arguments['courseId']);
	if(empty($course))
	{
		return array();
	}
	$tagIds = $course['tags'];
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


}
