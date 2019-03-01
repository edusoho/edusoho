<?php

namespace Biz\Article\Service;

use Biz\Article\ArticleException;
use Biz\Article\CategoryException;
use Biz\User\UserException;
use Biz\System\Annotation\Log;

interface ArticleService
{
    public function getArticle($id);

    /**
     * @param $currentArticleId
     *
     * @throws ArticleException
     * @throws CategoryException
     *
     * @return array
     */
    public function getArticlePrevious($currentArticleId);

    /**
     * @param $currentArticleId
     *
     * @throws ArticleException
     * @throws CategoryException
     *
     * @return array
     */
    public function getArticleNext($currentArticleId);

    public function getArticleByAlias($alias);

    public function findAllArticles();

    public function findArticlesByIds($ids);

    public function findArticlesByCategoryIds(array $categoryIds, $start, $limit);

    public function findArticlesCount(array $categoryIds);

    public function searchArticles(array $conditions, $sort, $start, $limit);

    public function countArticles($conditions);

    /**
     * @param $article
     *
     * @return mixed
     * @Log(module="article",action="create")
     */
    public function createArticle($article);

    /**
     * @param $id
     * @param $article
     *
     * @return mixed
     * @Log(module="article",action="update")
     */
    public function updateArticle($id, $article);

    public function batchUpdateOrg($articleIds, $orgCode);

    public function hitArticle($id);

    public function getArticleLike($articleId, $userId);

    /**
     * @param $id
     * @param $property
     *
     * @throws ArticleException
     *
     * @return int
     * @Log(module="article",action="update_property",funcName="getArticle",param="id")
     */
    public function setArticleProperty($id, $property);

    /**
     * @param $id
     * @param $property
     *
     * @throws ArticleException
     *
     * @return int
     * @Log(module="article",action="cancel_property",funcName="getArticle",param="id")
     */
    public function cancelArticleProperty($id, $property);

    /**
     * move article to trash.
     *
     * @param $id
     *
     * @throws ArticleException
     * @Log(module="article",action="trash",funcName="getArticle")
     */
    public function trashArticle($id);

    /**
     * delete article at trash.
     *
     * @param $id
     *
     * @throws ArticleException
     *
     * @return bool
     */
    public function deleteArticle($id);

    /**
     * batch delete articles at trash.
     *
     * @param array $ids
     *
     * @throws ArticleException
     *
     * @return mixed
     */
    public function deleteArticlesByIds(array $ids);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="article",action="removeThumb",funcName="getArticle")
     */
    public function removeArticlethumb($id);

    /**
     * like article.
     *
     * @param $articleId
     *
     * @throws ArticleException
     * @throws UserException
     *
     * @return array
     */
    public function like($articleId);

    /**
     * @param $articleId
     *
     * @throws ArticleException
     * @throws UserException
     */
    public function cancelLike($articleId);

    public function viewArticle($id);

    public function count($articleId, $field, $diff);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="article",action="publish",funcName="getArticle")
     */
    public function publishArticle($id);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="article",action="unpublish",funcName="getArticle")
     */
    public function unpublishArticle($id);

    public function changeIndexPicture($options);

    public function findPublishedArticlesByTagIdsAndCount($tagIds, $count);

    public function findRelativeArticles($articleId, $num = 3);
}
