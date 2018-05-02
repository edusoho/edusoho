<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Article\Service\ArticleService;
use Biz\Group\Service\ThreadService;

class PopularArticlePostsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取个人动态
     *
     *   count    必需
     *
     * @param array $arguments 参数
     *
     * @return array 个人动态
     */
    public function getData(array $arguments)
    {
        $limitCount = $arguments['count'] * 2;
        $articlePosts = $this->getThreadService()->searchPosts(
            array(
                'targetType' => 'article',
                'parentId' => 0,
                'latest' => 'week',
            ),
            array('ups' => 'DESC', 'createdTime' => 'DESC'),
            0,
            $limitCount
        );
        if ($limitCount > count($articlePosts)) {
            $conditions = array(
                'targetType' => 'article',
            );

            $excludeIds = ArrayToolkit::column($articlePosts, 'id');
            if (!empty($excludeIds)) {
                $conditions['excludeIds'] = $excludeIds;
            }

            $otherPosts = $this->getThreadService()->searchPosts(
                $conditions,
                array('ups' => 'DESC', 'createdTime' => 'DESC'),
                0,
                $limitCount - count($articlePosts)
            );

            $articlePosts = array_merge($articlePosts, $otherPosts);
        }

        $articleIds = ArrayToolkit::column($articlePosts, 'targetId');
        $publishedArticles = $this->getArticleService()->findArticlesByIds($articleIds);

        foreach ($articlePosts as $index => $articlePost) {
            if (empty($publishedArticles[$articlePost['targetId']])) {
                unset($articlePosts[$index]);
            }
        }
        $articlePosts = array_slice($articlePosts, 0, $arguments['count']);
        $userIds = ArrayToolkit::column($articlePosts, 'userId');

        $owners = $this->getUserService()->findUsersByIds($userIds);

        foreach ($articlePosts as $key => $articlePost) {
            $articlePosts[$key]['user'] = $owners[$articlePost['userId']];
            $articlePosts[$key]['article'] = $publishedArticles[$articlePost['targetId']];
        }

        return $articlePosts;
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ArticleService
     */
    private function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    /**
     * @return ThreadService
     */
    private function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }
}
