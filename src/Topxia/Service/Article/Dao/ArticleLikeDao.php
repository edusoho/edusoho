<?php
namespace Topxia\Service\Article\Dao;

interface ArticleLikeDao
{
    public function getArticleLike($id);

    public function getArticleLikeByArticleIdAndUserId($articleId, $userId);

    public function addArticleLike($articleLike);

    public function deleteArticleLikeByArticleIdAndUserId($articleId, $userId);

    public function findArticleLikesByUserId($userId);

    public function findArticleLikesByArticleId($articleId);

    public function findArticleLikesByArticleIds(array $articleIds);

    public function findArticleLikesByArticleIdsAndUserId(array $articleIds, $userId);
}
