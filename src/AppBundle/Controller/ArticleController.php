<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Article\ArticleException;
use Biz\Article\CategoryException;
use Biz\Article\Service\ArticleService;
use Biz\Article\Service\CategoryService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Biz\Taxonomy\TagException;
use Biz\Thread\Service\ThreadService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends BaseController
{
    public function indexAction(Request $request)
    {
        $categoryTree = $this->getCategoryService()->getCategoryTree();
        $conditions = $this->fillOrgCode(
            array(
                'status' => 'published',
            )
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->countArticles($conditions),
            $this->setting('article.pageNums', 10)
        );

        $latestArticles = $this->getArticleService()->searchArticles(
            $conditions,
            'published',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($latestArticles, 'categoryId');

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $featuredConditions = $this->fillOrgCode(
            array(
                'status' => 'published',
                'featured' => 1,
            )
        );

        $featuredArticles = $this->getArticleService()->searchArticles(
            $featuredConditions,
            'normal',
            0,
            5
        );

        $featuredCategories = array();

        foreach ($featuredArticles as $key => $value) {
            $featuredCategories[$value['id']] = $this->getCategoryService()->getCategory($value['categoryId']);
        }

        $promotedConditions = $this->fillOrgCode(
            array(
                'status' => 'published',
                'promoted' => 1,
            )
        );

        $promotedArticles = $this->getArticleService()->searchArticles(
            $promotedConditions,
            'normal',
            0,
            2
        );

        $promotedCategories = array();

        foreach ($promotedArticles as $key => $value) {
            $promotedCategories[$value['id']] = $this->getCategoryService()->getCategory($value['categoryId']);
        }

        return $this->render(
            'article/index.html.twig',
            array(
                'categoryTree' => $categoryTree,
                'latestArticles' => $latestArticles,
               'featuredArticles' => $featuredArticles,
                'featuredCategories' => $featuredCategories,
                'promotedArticles' => $promotedArticles,
                'promotedCategories' => $promotedCategories,
                'paginator' => $paginator,
                'categories' => $categories,
            )
        );
    }

    public function categoryNavAction(Request $request, $categoryCode)
    {
        list($rootCategories, $categories, $activeIds) = $this->getCategoryService()->makeNavCategories($categoryCode);

        return $this->render(
            'article/part/category.html.twig',
            array(
                'rootCategories' => $rootCategories,
                'categories' => $categories,
                'categoryCode' => $categoryCode,
                'activeIds' => $activeIds,
            )
        );
    }

    public function categoryAction(Request $request, $categoryCode)
    {
        $category = $this->getCategoryService()->getCategoryByCode($categoryCode);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $conditions = array(
            'categoryId' => $category['id'],
            'includeChildren' => true,
            'status' => 'published',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->countArticles($conditions),
            $this->setting('article.pageNums', 10)
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            'published',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        return $this->render(
            'article/list.html.twig',
            array(
                'categoryCode' => $categoryCode,
                'category' => $category,
                'articles' => $articles,
                'paginator' => $paginator,
                'categories' => $categories,
            )
        );
    }

    public function detailAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        if ($article['status'] != 'published') {
            return $this->createMessageResponse('error', '文章不是发布状态，请查看！');
        }

        $this->getArticleService()->viewArticle($id);

        $category = $this->getCategoryService()->getCategory($article['categoryId']);

        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'article', 'ownerId' => $id));

        $tagNames = ArrayToolkit::column($tags, 'name');

        $seoKeyword = '';

        if ($tags) {
            $seoKeyword = ArrayToolkit::column($tags, 'name');
            $seoKeyword = implode(',', $seoKeyword);
        }

        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($category['id']);

        $conditions = array(
            'targetId' => $id,
            'targetType' => 'article',
            'parentId' => 0,
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchPostsCount($conditions),
            10
        );

        $posts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $user = $this->getCurrentUser();

        $userLike = $this->getArticleService()->getArticleLike($id, $user['id']);

        $articleBody = $article['body'];

        $articleBody = strip_tags($articleBody, '');

        $articleBody = preg_replace('/ /', '', $articleBody);

        return $this->render(
            'article/detail.html.twig',
            array(
                'article' => $article,
                'tags' => $tags,
                'seoKeyword' => $seoKeyword,
                'seoDesc' => $articleBody,
                'breadcrumbs' => $breadcrumbs,
                'categoryName' => $category['name'],
                'categoryCode' => $category['code'],
                'posts' => $posts,
                'users' => $users,
                'paginator' => $paginator,
                'tagNames' => $tagNames,
                'userLike' => $userLike,
                'category' => $category,
                'service' => $this->getThreadService(),
            )
        );
    }

    public function postAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $post['content'] = $fields['content'];
            $post['targetType'] = 'article';
            $post['targetId'] = $id;

            $user = $this->getCurrentUser();

            if (!$user->isLogin()) {
                $this->createNewException(UserException::UN_LOGIN());
            }

            $post = $this->getThreadService()->createPost($post);

            return $this->render(
                'thread/part/post-item.html.twig',
                array(
                    'post' => $post,
                    'author' => $user,
                    'service' => $this->getThreadService(),
                    'postReplyUrl' => $this->generateUrl(
                        'article_post_reply',
                        array('articleId' => $id, 'postId' => $post['id'])
                    ),
                )
            );
        }
    }

    public function postReplyAction(Request $request, $articleId, $postId)
    {
        $fields = $request->request->all();
        $fields['content'] = $this->autoParagraph($fields['content']);
        $fields['targetId'] = $articleId;
        $fields['targetType'] = 'article';
        $fields['parentId'] = $postId;

        $post = $this->getThreadService()->createPost($fields);

        return $this->render(
            'thread/subpost-item.html.twig',
            array(
                'post' => $post,
                'author' => $this->getCurrentUser(),
                'service' => $this->getThreadService(),
            )
        );
    }

    public function postJumpAction(Request $request, $articleId, $postId)
    {
        $article = $this->getArticleService()->getArticle($articleId);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $post = $this->getThreadService()->getPost($postId);

        if ($post && $post['parentId']) {
            $post = $this->getThreadService()->getPost($post['parentId']);
        }

        if (empty($post)) {
            return $this->redirect(
                $this->generateUrl(
                    'article_detail',
                    array(
                        'id' => $articleId,
                    )
                )
            );
        }

        $conditions = array(
            'targetType' => 'article',
            'targetId' => $article['id'],
            'parentId' => 0,
            'greaterThanId' => $post['id'],
        );
        $position = $this->getThreadService()->searchPostsCount($conditions);

        $page = ceil($position / 10);

        return $this->redirect(
            $this->generateUrl(
                'article_detail',
                array(
                    'id' => $articleId,
                    'page' => $page,
                )
            )."#post-{$post['id']}"
        );
    }

    public function subpostsAction(Request $request, $targetId, $postId, $less = false)
    {
        $conditions = array(
            'parentId' => $postId,
        );
        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchPostsCount($conditions),
            10
        );

        $paginator->setBaseUrl(
            $this->generateUrl('article_post_subposts', array('targetId' => $targetId, 'postId' => $postId))
        );

        $posts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        return $this->render(
            'thread/subposts.html.twig',
            array(
                'parentId' => $postId,
                'targetId' => $targetId,
                'posts' => $posts,
                'users' => $users,
                'paginator' => $paginator,
                'less' => $less,
                'service' => $this->getThreadService(),
            )
        );
    }

    public function popularArticlesBlockAction()
    {
        $conditions = $this->fillOrgCode(
            array(
                'type' => 'article',
                'status' => 'published',
            )
        );

        $articles = $this->getArticleService()->searchArticles($conditions, 'popular', 0, 6);

        return $this->render(
            'article/popular-articles-block.html.twig',
            array(
                'articles' => $articles,
            )
        );
    }

    public function recommendArticlesBlockAction()
    {
        $conditions = array(
            'type' => 'article',
            'status' => 'published',
            'promoted' => 1,
        );

        $articles = $this->getArticleService()->searchArticles($conditions, 'normal', 0, 6);

        return $this->render(
            'article/recommend-articles-block.html.twig',
            array(
                'articles' => $articles,
            )
        );
    }

    public function tagAction(Request $request, $name)
    {
        $tag = $this->getTagService()->getTagByName($name);

        if (empty($tag)) {
            $this->createNewException(TagException::NOTFOUND_TAG());
        }

        $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType(
            array($tag['id']),
            'article'
        );

        $conditions = array(
            'status' => 'published',
            'articleIds' => ArrayToolkit::column($tagOwnerRelations, 'ownerId'),
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->countArticles($conditions),
            $this->setting('article.pageNums', 1)
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            'published',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        return $this->render(
            'article/list-articles-by-tag.html.twig',
            array(
                'articles' => $articles,
                'tag' => $tag,
                'categories' => $categories,
                'paginator' => $paginator,
            )
        );
    }

    protected function autoParagraph($text)
    {
        if (trim($text) !== '') {
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/\n\n+/", "\n\n", str_replace(array("\r\n", "\r"), "\n", $text));
            $texts = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
            $text = '';

            foreach ($texts as $txt) {
                $text .= '<p>'.nl2br(trim($txt, "\n"))."</p>\n";
            }

            $text = preg_replace('|<p>\s*</p>|', '', $text);
        }

        return $text;
    }

    protected function getRootCategory($categoryTree, $category)
    {
        $start = false;

        foreach (array_reverse($categoryTree) as $treeCategory) {
            if ($treeCategory['id'] == $category['id']) {
                $start = true;
            }

            if ($start && $treeCategory['depth'] == 1) {
                return $treeCategory;
            }
        }

        return array();
    }

    protected function getSubCategories($categoryTree, $rootCategory)
    {
        $categories = array();

        $start = false;

        foreach ($categoryTree as $treeCategory) {
            if ($start && ($treeCategory['depth'] == 1) && ($treeCategory['id'] != $rootCategory['id'])) {
                break;
            }

            if ($treeCategory['id'] == $rootCategory['id']) {
                $start = true;
            }

            if ($start == true) {
                $categories[] = $treeCategory;
            }
        }

        return $categories;
    }

    public function likeAction(Request $request, $articleId)
    {
        $this->getArticleService()->like($articleId);
        $article = $this->getArticleService()->getArticle($articleId);

        return $this->createJsonResponse($article);
    }

    public function cancelLikeAction(Request $request, $articleId)
    {
        $this->getArticleService()->cancelLike($articleId);
        $article = $this->getArticleService()->getArticle($articleId);

        return $this->createJsonResponse($article);
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Article:CategoryService');
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->getBiz()->service('Article:ArticleService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }
}
