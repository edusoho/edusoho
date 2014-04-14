<?php
namespace Topxia\Service\Article\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Article\Dao\ArticleDao;

class ArticleDaoImpl extends BaseDao implements ArticleDao
{
	protected $table = 'article';

	private $serializeFields = array(
            'tagIds' => 'json',
    );

	public function getArticle($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getArticleByAlias($alias)
	{
        $sql = "SELECT * FROM {$this->table} WHERE alias = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($alias)) ? : null;
	}

	public function findArticlesByCategoryIds(array $categoryIds, $start, $limit)
	{
		$this->filterStartLimit($start, $limit);
		if(empty($categoryIds)){ return array(); };
        $marks = str_repeat('?,', count($categoryIds) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE categoryId in ({$marks}) ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, $categoryIds);
	}

	public function findArticlesCount(array $categoryIds)
	{
		if(empty($categoryIds)){ return array(); };
        $marks = str_repeat('?,', count($categoryIds) - 1) . '?';
		$sql = "SELECT COUNT(id) FROM {$this->table} WHERE categoryId in ({$marks})";

        return $this->getConnection()->fetchColumn($sql, $categoryIds);
	}

	public function searchArticles($conditions, $orderBy, $start, $limit)
	{
		$this->filterStartLimit($start, $limit);
		$builder = $this->_createSearchQueryBuilder($conditions)
			->select('*')
			->addOrderBy($orderBy[0], $orderBy[1])
			->setFirstResult($start)
			->setMaxResults($limit);
		return $builder->execute()->fetchAll() ? : array();
	}

	public function searchArticleCount($conditions)
	{
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
	}

	public function addArticle($article)
	{
        $affected = $this->getConnection()->insert($this->table, $article);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }
        return $this->getArticle($this->getConnection()->lastInsertId());
	}

	public function updateArticle($id, $Article)
	{
        $this->getConnection()->update($this->table, $Article, array('id' => $id));
        return $this->getArticle($id);
	}

	public function deleteArticle($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}	

	private function _createSearchQueryBuilder($conditions)
	{
		$conditions = array_filter($conditions);
		
		if(array_key_exists('property',$conditions)){
			$key = $conditions['property'];
			$conditions[$key] = 1;
		}

		if(isset($conditions['keywords'])){
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->from($this->table, 'article')
			->andWhere('status = :status')
			->andWhere('categoryId = :categoryId')
			->andWhere('featured = :featured')
			->andWhere('promoted = :promoted')
			->andWhere('sticky = :sticky')
			->andWhere('title LIKE :keywords')
			->andWhere('hasPicture = :hasPicture')
			->andWhere('categoryId = :categoryId');

		return $builder;
	}
}