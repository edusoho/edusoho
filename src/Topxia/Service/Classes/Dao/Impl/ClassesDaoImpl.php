<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\ClassesDao;

class ClassesDaoImpl extends BaseDao implements ClassesDao
{

    public function getClass($id)
    {
        $sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function searchClasses($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $defaultOrderby = array('year' => 'DESC', 'gradeId' => 'ASC', 'name' => 'ASC');
        $orderBy = array_merge($defaultOrderby, $orderBy);
        $builder = $this->_createSearchQueryBuilder($conditions)
        ->select('*')
        ->setFirstResult($start)
        ->setMaxResults($limit);
        
        foreach ($orderBy as $key => $value) {
            $builder->addOrderBy($key, $value);
        }

        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchClassCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function createClass($class)
    {
        $affected = $this->getConnection()->insert(self::TABLENAME, $class);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getClass($this->getConnection()->lastInsertId());
    }

    private function _createSearchQueryBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
        ->from(self::TABLENAME, 'class')
        ->andWhere('enabled = :enabled')
        ->andWhere('gradeId = :gradeId')
        ->andWhere('year = :year');

        return $builder;
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }
}