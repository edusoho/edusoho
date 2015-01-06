<?php
namespace Topxia\Service\Article;

interface ArticleService
{
	public function getArticle($id);
	
	public function getArticlePrevious($currentArticleId);

	public function getArticleNext($currentArticleId);

	public function getArticleByAlias($alias);

	public function findArticlesByCategoryIds(array $categoryIds, $start, $limit);

	public function findArticlesCount(array $categoryIds);

	public function searchArticles(array $conditions, $sort, $start, $limit);

	public function searchArticlesCount($conditions);

	public function createArticle($article);

	public function updateArticle($id, $Article);

	public function hitArticle($id);

	public function setArticleProperty($id, $property);

	public function cancelArticleProperty($id, $property);
	
	public function trashArticle($id);
	
	public function removeArticlethumb($id);

	public function deleteArticle($id);

	public function deleteArticlesByIds($ids);
	
	public function publishArticle($id);
	
	public function unpublishArticle($id);

	public function changeIndexPicture($filePath, $options);

	public function findPublishedArticlesByTagIdsAndCount($tagIds,$count);
}