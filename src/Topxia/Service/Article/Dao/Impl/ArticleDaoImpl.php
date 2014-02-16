<?php
namespace Topxia\Service\Article\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Article\Dao\ArticleDao;

class ArticleDaoImpl extends BaseDao implements ArticleDao
{
	protected $table = 'Article';

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

	public function addArticle($Article)
	{
        $affected = $this->getConnection()->insert($this->table, $Article);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }
        return $this->getConnection()->lastInsertId();
	}

	public function updateArticle($id, $Article)
	{
        return $this->getConnection()->update($this->table, $Article, array('id' => $id));
	}

	public function deleteArticle($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	private function _createSearchQueryBuilder($conditions)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->from($this->table, 'Article')
			->andWhere('type = :type')
			->andWhere('status = :status')
			->andWhere('title LIKE :keywords');

		if (isset($conditions['categoryIds'])) {
			$categoryIds = array();
			foreach ($conditions['categoryIds'] as $categoryId) {
				if (ctype_digit($categoryId)) {
					$categoryIds[] = $categoryId;
				}
			}
			if ($categoryIds) {
				$categoryIds = join(',', $categoryIds);
				$builder->andStaticWhere("categoryId IN ($categoryIds)");
			}
		}

		return $builder;
	}
}