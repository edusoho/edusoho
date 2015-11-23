<?php
namespace Mooc\Service\Organization\Dao\Impl;

use Mooc\Service\Organization\Dao\OrganizationDao;
use Topxia\Service\Common\BaseDao;

class OrganizationDaoImpl extends BaseDao implements OrganizationDao
{
    const TABLENAME = 'organization';

    public function getOrganization($id)
    {
        $sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addOrganization($organization)
    {
        $affected = $this->getConnection()->insert(self::TABLENAME, $organization);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert organization error.');
        }

        return $this->getOrganization($this->getConnection()->lastInsertId());
    }

    public function searchOrganizationCount($conditions)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(*)')
        ;

        return $builder->execute()->fetchColumn(0);
    }

    public function searchOrganizations($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function updateOrganization($id, $fields)
    {
        $this->getConnection()->update(self::TABLENAME, $fields, array('id' => $id));
        return $this->getOrganization($id);
    }

    public function findAllOrganizations()
    {
        $sql = "SELECT * FROM {$this->getTableName()};";
        return $this->getConnection()->fetchAll($sql);
    }

    public function deleteOrganization($id)
    {
        return $this->getConnection()->delete(self::TABLENAME, array('id' => $id));
    }

    protected function getTableName()
    {
        return self::TABLENAME;
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'organization')
            ->andWhere('code = :code')
            ->andWhere('name = :name')
            ->andWhere('parentId = :parentId')
            ->andWhere('id IN ( :organizationIds )')
        ;

        return $builder;
    }
}
