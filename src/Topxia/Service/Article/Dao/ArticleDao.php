<?php

namespace Topxia\Service\Article\Dao;

interface ArticleDao
{
	public function getArticle($id);

	public function getArticleByAlias($alias);

	public function findArticlesByCategoryIds(array $categoryIds, $start, $limit);

	public function findArticlesCount(array $categoryIds);

	public function searchArticles($conditions, $orderBy, $start, $limit);

	public function searchArticleCount($conditions);

	public function addArticle($Article);

	public function updateArticle($id, $Article);

	public function deleteArticle($id);
}