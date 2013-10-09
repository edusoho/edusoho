<?php
namespace Topxia\Service\Photo\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Photo\Dao\PhotoFileDao;

class PhotoFileDaoImpl extends BaseDao implements PhotoFileDao
{
    protected $table = 'photo_file';

    public function getFile($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function findFileByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE groupId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchFiles($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function findFiles($start, $limit){

    }
    
    public function searchFileCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }


    private function _createSearchQueryBuilder($conditions)
    {

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'photo_file')
            ->andWhere('groupId = :groupId')
            ->andWhere('userId = :userId');    
    }

    public function addFile($file)
    {
        var_dump($file);
        $affected = $this->getConnection()->insert($this->table, $file);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getFile($this->getConnection()->lastInsertId());
    }

    public function updateFile($id, $fields)
    {
        $count = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $count>0?$this->getFile($id):null;
    }

    public function deleteFile($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }
}