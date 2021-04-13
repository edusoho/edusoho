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
        $conditions = array_merge(['targetType' => 'article'], $request->query->all());

        $conditions = $this->prepareConditions($conditions);

        $reviews = [];
        $articles = [];
        $users = [];

        $paginatorTotal = empty($conditions) ? 0 : $this->getThreadService()->searchPostsCount($conditions);

        $paginator = new Paginator(
            $request,
            $paginatorTotal,
            20
        );

        if (!empty($conditions)) {
            $reviews = $this->getThreadService()->searchPosts(
                $conditions,
                ['createdTime' => 'DESC'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
            $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
            $articles = $this->getArticleService()->findArticlesByIds(ArrayToolkit::column($reviews, 'targetId'));
        }

        return $this->render('admin-v2/operating/article-review/index.html.twig', [
            'reviews' => $reviews,
            'articles' => $articles,
            'paginator' => $paginator,
            'users' => $users,
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
        $conditions['parentId'] = 0;
        $conditions['threadId'] = 0;

        if (!empty($conditions['articleTitle'])) {
            $articles = $this->getArticleService()->findArticleByLikeTitle(trim($conditions['articleTitle']));
            unset($conditions['articleTitle']);
            $targetIds = ArrayToolkit::column($articles, 'id');
            if (empty($targetIds)) {
                return [];
            }
            $conditions['targetIds'] = ArrayToolkit::column($articles, 'id');
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
