<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 13:43
 */

namespace Custom\Service\School\Dao\Impl;


use Custom\Service\School\Dao\SchoolDao;
use Topxia\Service\Common\BaseDao;

class SchoolDaoImpl extends BaseDao implements SchoolDao
{
    const TABLENAME = 'school_organization' ;

    public function getSchoolOrganization($id)
    {
        $sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addSchoolOrganization($organization)
    {
        $affected = $this->getConnection()->insert(self::TABLENAME, $organization);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getSchoolOrganization($this->getConnection()->lastInsertId());
    }

    public function searchSchoolOrganizationCount($conditions)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(*)')
            ;

        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchSchoolOrganization($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function updateSchoolOrganization($id, $fields)
    {
        $this->getConnection()->update(self::TABLENAME, $fields, array('id' => $id));
        return $this->getSchoolOrganization($id);
    }

    public function findAllSchoolOrganization()
    {
        $sql = "SELECT * FROM {$this->getTableName()};";
        return $this->getConnection()->fetchAll($sql);
    }

    public function deleteSchoolOrganization($id)
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
            ->from(self::TABLENAME, 'school_organization')
            ->andWhere('code = :code')
            ->andWhere('name = :name')
            ->andWhere('parentId = :parentId')
            ;

        return $builder;

    }

}