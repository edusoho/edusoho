<?php
namespace Topxia\Service\Article;

interface ArticleService
{
	public function getArticle($id);

	public function getArticleByAlias($alias);

	public function searchArticles(array $conditions, array $orderBy, $start, $limit);

	public function searchArticleCount($conditions);

	public function createArticle($Article);

	public function updateArticle($id, $Article);

	public function trashArticle($id);

	public function deleteArticle($id);

	public function deleteArticlesByIds($ids);

    // @todo
    // public function publishArticle($id);

	public function isAliasAvaliable($alias);
}