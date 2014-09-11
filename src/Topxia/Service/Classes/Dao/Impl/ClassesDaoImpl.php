<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\ClassesDao;

class ClassesDaoImpl extends BaseDao implements ClassesDao
{
    protected $cached = array();

    public function getClass($id)
    {
        $self = $this;
        return $this->cachedCall($id, function($id) use ($self) {
            $sql = "SELECT * FROM {$self->getTablename()} WHERE id = ? LIMIT 1";
            return $self->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        });
    }

    public function findClassesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->getTablename()} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findClassesByHeadTeacherId($headTeacherId)
    {
        $sql ="SELECT * FROM {$this->getTablename()} WHERE headTeacherId = ?;";
        return $this->getConnection()->fetchAll($sql, array($headTeacherId)) ? : null;
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

    public function editClass($fields, $id)
    {
        $this->getConnection()->update(self::TABLENAME, $fields, array('id' => $id));
        return $this->getClass($id);
    }
    
    public function updateClassStudentNum($num,$id){
        $sql ="UPDATE {$this->getTablename()} SET studentNum=studentNum+({$num}) WHERE id ={$id};";
        return $this->getConnection()->exec($sql);
    }

    public function deleteClass($id)
    {
        return $this->getConnection()->delete(self::TABLENAME, array('id' => $id));
    }

    private function _createSearchQueryBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
        ->from(self::TABLENAME, 'class')
        ->andWhere('enabled = :enabled')
        ->andWhere('gradeId = :gradeId')
        ->andWhere('headTeacherId = :headTeacherId')
        ->andWhere('year = :year');

        return $builder;
    }

    public function getTablename()
    {
        return self::TABLENAME;
    }

    protected function cachedCall($id, $callback)
    {
        if (isset($this->cached[$id])) {
            return $this->cached[$id];
        }

        $this->cached[$id] = $callback($id);

        return $this->cached[$id];
    }

}