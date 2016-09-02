<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\FileUsedDao;

class FileUsedDaoImpl extends BaseDao implements FileUsedDao
{
    protected $table = 'file_used';

    public function get($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        });
    }

    public function create($fileUsed)
    {
        $affected = $this->getConnection()->insert($this->getTable(), $fileUsed);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException("Insert {$this->getTable()} error.");
        }

        return $this->get($this->getConnection()->lastInsertId());
    }

    public function update($id, $fields)
    {
        $this->getConnection()->update($this->getTable(), $fields, array('id' => $id));
        $this->clearCached();
        return $this->get($id);
    }

    public function delete($id)
    {
        $result = $this->getConnection()->delete($this->getTable(), array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function search($conditions, $order, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($order[0], $order[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function count($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['targetTypes'])) {
            unset($conditions['targetType']);
        }
        if (isset($conditions['targetIds'])) {
            unset($conditions['targetId']);
        }
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'file_used')
            ->andWhere('id = :id')
            ->andWhere('type = :type')
            ->andWhere('targetType = :targetType')
            ->andWhere('targetType IN ( :targetTypes )')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetId IN ( :targetIds )')
            ->andWhere('fileId = :fileId')
        ;
        return $builder;
    }
}
