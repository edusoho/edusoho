<?php

namespace Biz\Article\Dao\Impl;

use Biz\Article\Dao\CategoryDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CategoryDaoImpl extends GeneralDaoImpl implements CategoryDao
{
    protected $table = 'article_category';

    public function getByParentId($parentId)
    {
        return $this->getByFields(array(
            'parentId' => $parentId,
        ));
    }

    public function findByParentId($parentId)
    {
        return $this->findByFields(array(
            'parentId' => $parentId,
        ));
    }

    public function findAllPublishedByParentId($parentId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE parentId = ? AND published = ? order by weight";

        return $this->db()->fetchAll($sql, array($parentId, 1)) ?: array();
    }

    public function findByCode($code)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE code = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($code)) ?: array();
    }

    public function searchByParentId($parentId, $orderBy, $start, $limit)
    {
        return $this->search(
            array(
                'parentId' => $parentId,
            ),
            $orderBy,
            $start,
            $limit
        );
    }

    public function countByParentId($parentId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE  parentId = ?";

        return $this->db()->fetchColumn($sql, array($parentId));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()} ORDER BY weight ASC";

        return $this->db()->fetchAll($sql) ?: array();
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'parentId = :parentId',
                'published = :published',
            ),
        );
    }
}
