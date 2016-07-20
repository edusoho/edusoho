<?php
namespace Topxia\Service\Article;

interface ArticleService
{
    public function getArticle($id);

    public function getArticlePrevious($currentArticleId);

    public function getArticleNext($currentArticleId);

    public function getArticleByAlias($alias);

    public function findAllArticles();

    public function findArticlesByIds($ids);

    public function findArticlesByCategoryIds(array $categoryIds, $start, $limit);

    public function findArticlesCount(array $categoryIds);

    public function searchArticles(array $conditions, $sort, $start, $limit);

    public function searchArticlesCount($conditions);

    public function createArticle($article);

    public function updateArticle($id, $article);

    public function batchUpdateOrg($articleIds, $orgCode);

    public function hitArticle($id);

    public function getArticleLike($articleId, $userId);

    public function setArticleProperty($id, $property);

    public function cancelArticleProperty($id, $property);

    public function trashArticle($id);

    public function removeArticlethumb($id);

    public function like($articleId);

    public function cancelLike($articleId);

    public function count($articleId, $field, $diff);

    public function publishArticle($id);

    public function unpublishArticle($id);

    public function changeIndexPicture($options);

    public function findPublishedArticlesByTagIdsAndCount($tagIds, $count);

    public function findRelativeArticles($articleId, $num = 3);

}
