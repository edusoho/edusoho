<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 12/12/2016
 * Time: 17:12.
 */

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\CategoryDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CategoryDaoImpl extends GeneralDaoImpl implements CategoryDao
{
    protected $table = 'category';

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function findByGroupId($groupId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE groupId = ? ORDER BY weight ASC";

        return $this->db()->fetchAll($sql, array($groupId)) ?: array();
    }

    public function findByGroupIdAndOrgId($groupId, $orgId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE groupId = ? AND orgId =?  ORDER BY weight ASC";

        return $this->db()->fetchAll($sql, array($groupId, $orgId)) ?: array();
    }

    public function findByParentId($parentId, $orderBy, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE parentId = ? {$orderBy} ";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($parentId)) ?: array();
    }

    public function findAllByParentId($parentId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE parentId = ? ORDER BY weight ASC";

        return $this->db()->fetchAll($sql, array($parentId)) ?: array();
    }

    public function findByGroupIdAndParentId($groupId, $parentId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE groupId = ? AND parentId = ? ORDER BY weight ASC";

        return $this->db()->fetchAll($sql, array($groupId, $parentId)) ?: array();
    }

    public function findCountByParentId($parentId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE  parentId = ?";

        return $this->db()->fetchColumn($sql, array($parentId));
    }

    public function findByIds(array $ids)
    {
        $ids = array_filter(array_unique($ids));

        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE id IN ({$marks});";

        return $this->db()->fetchAll($sql, array_values($ids)) ?: array();
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()}";

        return $this->db()->fetchAll($sql) ?: array();
    }

    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int) $start;
        $limit = (int) $limit;
    }

    public function declares()
    {
        return array(
        );
    }
}
