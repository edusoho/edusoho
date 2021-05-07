<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Article\Service\Impl\ArticleServiceImpl;
use Biz\Thread\Service\Impl\ThreadServiceImpl;
use Symfony\Component\HttpFoundation\Request;

class ArticleReviewController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchPostsCount($conditions),
            20
        );

        $reviews = $this->getThreadService()->searchPosts(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $articles = $this->getArticleService()->findArticlesByIds(ArrayToolkit::column($reviews, 'targetId'));

        return $this->render('admin-v2/operating/article-review/index.html.twig', [
            'paginator' => $paginator,
            'reviews' => $reviews,
            'users' => $users,
            'articles' => $articles,
        ]);
    }

    public function deleteReviewAction(Request $request, $id)
    {
        $this->getThreadService()->deletePost($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getThreadService()->deletePost($id);
        }

        return $this->createJsonResponse(true);
    }

    protected function prepareConditions($conditions)
    {
        $conditions['targetType'] = 'article';
        $conditions['parentId'] = 0;
        $conditions['threadId'] = 0;

        if (!empty($conditions['articleTitle'])) {
            $articles = $this->getArticleService()->findArticlesByLikeTitle(trim($conditions['articleTitle']));
            unset($conditions['articleTitle']);
            $conditions['targetIds'] = empty($articles) ? [-1] : ArrayToolkit::column($articles, 'id');
        }

        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            unset($conditions['author']);
            $conditions['userIds'] = $user['id'] ? $user['id'] : -1;
        }

        return $conditions;
    }

    /**
     * @return ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return ArticleServiceImpl
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }
}
