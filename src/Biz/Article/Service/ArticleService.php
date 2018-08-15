<?php

namespace Biz\Article\Service;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Biz\System\Annotation\Log;

interface ArticleService
{
    public function getArticle($id);

    /**
     * @param $currentArticleId
     *
     * @throws NotFoundException
     *
     * @return array
     */
    public function getArticlePrevious($currentArticleId);

    /**
     * @param $currentArticleId
     *
     * @throws NotFoundException
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

    public function updateArticle($id, $article);

    public function batchUpdateOrg($articleIds, $orgCode);

    public function hitArticle($id);

    public function getArticleLike($articleId, $userId);

    /**
     * @param $id
     * @param $property
     *
     * @throws NotFoundException
     *
     * @return int
     */
    public function setArticleProperty($id, $property);

    /**
     * @param $id
     * @param $property
     *
     * @throws NotFoundException
     *
     * @return int
     */
    public function cancelArticleProperty($id, $property);

    /**
     * move article to trash.
     *
     * @param $id
     *
     * @throws NotFoundException
     */
    public function trashArticle($id);

    /**
     * delete article at trash.
     *
     * @param $id
     *
     * @throws NotFoundException
     *
     * @return bool
     */
    public function deleteArticle($id);

    /**
     * batch delete articles at trash.
     *
     * @param array $ids
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function deleteArticlesByIds(array $ids);

    public function removeArticlethumb($id);

    /**
     * like article.
     *
     * @param $articleId
     *
     * @throws NotFoundException
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function like($articleId);

    /**
     * @param $articleId
     *
     * @throws NotFoundException
     */
    public function cancelLike($articleId);

    public function viewArticle($id);

    public function count($articleId, $field, $diff);

    public function publishArticle($id);

    public function unpublishArticle($id);

    public function changeIndexPicture($options);

    public function findPublishedArticlesByTagIdsAndCount($tagIds, $count);

    public function findRelativeArticles($articleId, $num = 3);
}
