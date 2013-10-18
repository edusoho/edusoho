<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\DiskFileDao;
    
class DiskFileDaoImpl extends BaseDao implements DiskFileDao
{
    protected $table = 'user_disk_file';

    public function getFile($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getFileByConvertHash($hash)
    {
        $sql = "SELECT * FROM {$this->table} WHERE convertHash = ? order by createdTime DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($hash)) ? : null;
    }

    public function findFilesByIds($ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchFiles($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchFileCount($conditions)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function deleteFile($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
    public function addFile(array $file)
    {
        $affected = $this->getConnection()->insert($this->table, $file);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user disk file error.');
        }
        return $this->getFile($this->getConnection()->lastInsertId());
    }

    public function updateFile($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getFile($id);
    }

    private function createSearchQueryBuilder($conditions)
    {
        
        if (isset($conditions['filename'])) {
            $conditions['filenameLike'] = "%{$conditions['filename']}%";
            unset($conditions['filename']);
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('filename LIKE :filenameLike')
            ->andWhere('userId = :userId')
            ->andWhere('type = :type');
    }

}