<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;

class PopularArticlePostsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取个人动态
     *
     *   count    必需
     * @param  array $arguments 参数
     * @return array 个人动态
     */
    public function getData(array $arguments)
    {
        $articlePosts = $this->getThreadService()->searchPosts(
            array(
                'targetType' => 'article',
                'parentId' => 0,
                'latest' => 'week'
            ),
            array('ups' => 'DESC', 'createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        if ($arguments['count'] > count($articlePosts)) {
            $excludeIds = ArrayToolkit::column($articlePosts, 'id');
            $otherPosts = $this->getThreadService()->searchPosts(
                array(
                    'targetType' => 'article',
                    'excludeIds' => $excludeIds
                ),
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
            $articlePosts[$key]['user'] = $owners[$articlePost['userId']];
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
