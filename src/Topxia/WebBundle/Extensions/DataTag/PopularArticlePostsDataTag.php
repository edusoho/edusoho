<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;

class PopularArticlePostsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取个人动态
     *
     *   count    必需
     * @param  array $arguments     参数
     * @return array 个人动态
     */
    public function getData(array $arguments)
    {
        $publishedActicles = $this->getArticleService()->searchArticles(
            array('status' => 'published'),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );
        $targetIds = ArrayToolkit::column($publishedActicles, 'id');

        $articlePosts = $this->getThreadService()->searchPosts(
            array(
                'targetType' => 'article',
                'parentId'   => 0,
                'targetIds'  => $targetIds,
                'latest'     => 'week'
            ),
            array('ups' => 'DESC', 'createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        if ($arguments['count'] > count($articlePosts)) {
            $conditions = array(
                'targetType' => 'article',
                'targetIds'  => $targetIds
            );

            $excludeIds = ArrayToolkit::column($articlePosts, 'id');
            if (!empty($excludeIds)) {
                $conditions['excludeIds'] = $excludeIds;
            }

            $otherPosts = $this->getThreadService()->searchPosts(
                $conditions,
                array('ups' => 'DESC', 'createdTime' => 'DESC'),
                0,
                $arguments['count'] - count($articlePosts)
            );

            $articlePosts = array_merge($articlePosts, $otherPosts);
        }

        $userIds = ArrayToolkit::column($articlePosts, 'userId');

        $owners = $this->getUserService()->findUsersByIds($userIds);

        $articleIds = ArrayToolkit::column($articlePosts, 'targetId');

        $articles = $this->getArticleService()->findArticlesByIds($articleIds);

        foreach ($articlePosts as $key => $articlePost) {
            $articlePosts[$key]['user']    = $owners[$articlePost['userId']];
            $articlePosts[$key]['article'] = $articles[$articlePost['targetId']];
        }

        return $articlePosts;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}
