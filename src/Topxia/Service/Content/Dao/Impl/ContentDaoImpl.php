<?php
namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\ContentDao;

class ContentDaoImpl extends BaseDao implements ContentDao
{
	protected $table = 'content';

	public function getContent($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getContentByAlias($alias)
	{
        $sql = "SELECT * FROM {$this->table} WHERE alias = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($alias)) ? : null;
	}

	public function searchContents($conditions, $orderBy, $start, $limit)
	{
		$this->filterStartLimit($start, $limit);
		$builder = $this->_createSearchQueryBuilder($conditions)
			->select('*')
			->addOrderBy($orderBy[0], $orderBy[1])
			->setFirstResult($start)
			->setMaxResults($limit);
		return $builder->execute()->fetchAll() ? : array();
	}

	public function searchContentCount($conditions)
	{
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
	}

	public function addContent($content)
	{
        $affected = $this->getConnection()->insert($this->table, $content);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert content error.');
        }
        return $this->getConnection()->lastInsertId();
	}

	public function updateContent($id, $content)
	{
        return $this->getConnection()->update($this->table, $content, array('id' => $id));
	}

	public function deleteContent($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	private function _createSearchQueryBuilder($conditions)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->from($this->table, 'content')
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